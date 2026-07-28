<?php

namespace App\Console\Commands;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Loads the SIARC 2026 artisans as unpublished, claimable shop profiles.
 *
 * Deliberately a command and not a seeder: it takes a file that lives outside
 * the repository, it must be safe to re-run, and it must never be swept into
 * `db:seed` on a fresh install.
 *
 * NOTHING HERE MAY EMAIL ANYONE. The dataset carries no email addresses, every
 * imported user is written with a null email, and the command fakes the mailer
 * before it starts — so a stray send from a model event is captured instead of
 * delivered. Those are three independent reasons no artisan can be contacted;
 * removing any one of them still leaves the guarantee intact.
 */
class ImportSiarcArtisans extends Command
{
    protected $signature = 'siarc:import
                            {file : Path to the .xlsx export (sheet "Donnees brutes")}
                            {--dry-run : Parse and resolve everything, write nothing}';

    protected $description = 'Import SIARC 2026 artisans as unpublished, claimable profiles';

    /** Column positions in the "Donnees brutes" sheet, 0-indexed. */
    private const COL_CODE     = 1;
    private const COL_NAME     = 2;
    private const COL_OBJET    = 11;
    private const COL_REGISTRY = 12;
    private const COL_PHONE    = 13;
    private const COL_AGEBAND  = 15;
    private const COL_REGION   = 16;
    private const COL_SEX      = 17;
    private const COL_FILIERE  = 18;
    private const COL_COMMUNE  = 19;
    private const COL_METIER   = 20;
    private const COL_CORPS    = 21;

    /** Taxonomy lookups, built once. */
    private array $byLevel = [];
    private array $regions = [];
    private array $cities  = [];

    /** SIARC's 16 competition filières mapped onto official level-2 branches. */
    private array $filiereMap = [];

    /**
     * The competition groups artisans into sixteen filières of its own naming,
     * which share no wording with the official nomenclature. Mapped by hand,
     * once, to the closest level-2 branch — used only as the final fallback for
     * artisans whose métier is blank or unrecognised.
     */
    private const FILIERE_TO_BRANCH = [
        'peinture batik'                  => 'Art et décoration',
        'arts decoratif'                  => 'Art et décoration',
        'sculture sur bois et sur pierre' => 'Bois et assimilés, mobilier et ameublement',
        'menuiserie ebenisterie'          => 'Bois et assimilés, mobilier et ameublement',
        'vannerie et tissage'             => 'Bois et assimilés, mobilier et ameublement',
        'textile et habillement'          => 'Textile, peaux et cuirs',
        'maroquinerie'                    => 'Textile, peaux et cuirs',
        'bijouterie et accessoire de mode' => 'Art et décoration',
        'poterie et ceramique'            => 'Art et décoration',
        'forge soudure bronze et metaux'  => 'Construction métallique, aluminium, plastique, mécanique, électromécanique, électronique, électrotechnique, électricité et petites activités de transport',
        'machinerie et outils'            => 'Construction métallique, aluminium, plastique, mécanique, électromécanique, électronique, électrotechnique, électricité et petites activités de transport',
        'transformation agroalimantaire'  => 'Agro-alimentaire, alimentation, restauration',
        'cosmetique et pharmacope'        => 'Santé, hygiène et soins corporels',
        'coiffure et esthetique'          => 'Santé, hygiène et soins corporels',
        'recuperation et recyclage'       => 'Mines et carrières, construction et bâtiment',
        'transformation et invention div'  => 'Art et décoration',
    ];

    private array $tally = [
        'exact' => 0, 'prefix' => 0, 'corps' => 0, 'filiere' => 0, 'unplaced' => 0,
        'created' => 0, 'updated' => 0, 'phone_set' => 0, 'phone_skipped' => 0,
    ];

    public function handle(): int
    {
        // Belt and braces: even a model observer firing a notification cannot
        // reach a real inbox from here.
        Mail::fake();

        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $rows = $this->readSheet($path);
        if ($rows === null) {
            return self::FAILURE;
        }

        $this->info(sprintf('Parsed %d artisan row(s).', count($rows)));
        $this->buildLookups();

        $dry = (bool) $this->option('dry-run');
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            $dry ? $this->resolveOnly($row) : DB::transaction(fn () => $this->importOne($row));
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->report($dry);

        return self::SUCCESS;
    }

    /**
     * Read the sheet via PHP's zip + SimpleXML rather than a spreadsheet
     * package, so the import adds no dependency for a job it does once.
     */
    private function readSheet(string $path): ?array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            $this->error('Could not open the workbook.');
            return null;
        }

        // Shared strings: xlsx stores most cell text here and references it by index.
        $shared = [];
        if ($xml = $zip->getFromName('xl/sharedStrings.xml')) {
            foreach (simplexml_load_string($xml)->si as $si) {
                $shared[] = trim((string) ($si->t ?? implode('', array_map(
                    fn ($r) => (string) $r->t,
                    iterator_to_array($si->r ?? [])
                ))));
            }
        }

        $sheetPath = $this->locateSheet($zip, 'Donnees brutes');
        if (! $sheetPath) {
            $this->error('Sheet "Donnees brutes" not found in the workbook.');
            $zip->close();
            return null;
        }

        $sheet = simplexml_load_string($zip->getFromName($sheetPath));
        $zip->close();

        $rows = [];
        foreach ($sheet->sheetData->row as $r) {
            $cells = [];
            foreach ($r->c as $c) {
                $ref = (string) $c['r'];
                $col = $this->columnIndex(preg_replace('/\d+/', '', $ref));
                $val = (string) $c->v;
                if ((string) $c['t'] === 's') {
                    $val = $shared[(int) $val] ?? '';
                } elseif ((string) $c['t'] === 'inlineStr') {
                    $val = (string) $c->is->t;
                }
                $cells[$col] = trim($val);
            }
            $rows[] = $cells;
        }

        array_shift($rows); // header

        // A row without a name is padding, not an artisan.
        return array_values(array_filter($rows, fn ($r) => filled($r[self::COL_NAME] ?? null)));
    }

    private function locateSheet(\ZipArchive $zip, string $wanted): ?string
    {
        $wb = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));

        $target = [];
        foreach ($rels->Relationship as $rel) {
            $target[(string) $rel['Id']] = (string) $rel['Target'];
        }

        foreach ($wb->sheets->sheet as $s) {
            if ((string) $s['name'] !== $wanted) {
                continue;
            }
            $rid = (string) $s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
            $t = $target[$rid] ?? null;
            return $t ? 'xl/' . ltrim(str_replace('/xl/', '', $t), '/') : null;
        }

        return null;
    }

    private function columnIndex(string $letters): int
    {
        $n = 0;
        foreach (str_split($letters) as $ch) {
            $n = $n * 26 + (ord(strtoupper($ch)) - 64);
        }
        return $n - 1;
    }

    /**
     * Accent-insensitive key used for every fuzzy match in this importer.
     *
     * Typographic punctuation is flattened to ASCII *before* transliteration.
     * The export writes curly apostrophes (U+2019) where the taxonomy uses
     * straight ones, and iconv drops the curly form rather than converting it —
     * so "Producteur d’huile" keyed to "producteur dhuile" while the very same
     * trade in the database keyed to "producteur d huile", and 54 artisans went
     * unplaced against entries that were sitting right there.
     */
    private function key(?string $s): string
    {
        $s = (string) $s;
        $s = strtr($s, [
            "\u{2019}" => "'", "\u{2018}" => "'", "\u{02BC}" => "'",
            "\u{201C}" => '"', "\u{201D}" => '"',
            "\u{2013}" => '-', "\u{2014}" => '-', "\u{00A0}" => ' ',
        ]);
        $s = \Normalizer::isNormalized($s) ? $s : \Normalizer::normalize($s);
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower($s))));
    }

    private function buildLookups(): void
    {
        foreach ([1, 2, 3, 4] as $level) {
            $this->byLevel[$level] = DB::table('industries')
                ->where('is_active', true)->where('level', $level)
                ->pluck('id', 'name_fr')
                ->mapWithKeys(fn ($id, $name) => [$this->key($name) => $id])
                ->all();
        }

        $this->regions = DB::table('regions')->pluck('id', 'name_fr')
            ->mapWithKeys(fn ($id, $n) => [$this->key($n) => $id])->all();

        $this->cities = DB::table('cities')->pluck('id', 'name_fr')
            ->mapWithKeys(fn ($id, $n) => [$this->key($n) => $id])->all();

        // Resolve the hand-written filière map to real ids, and say so loudly if
        // a branch name has drifted — a silently unmapped filière would send
        // artisans back to "unplaced" with no explanation.
        foreach (self::FILIERE_TO_BRANCH as $filiere => $branch) {
            $id = $this->byLevel[2][$this->key($branch)] ?? $this->byLevel[1][$this->key($branch)] ?? null;
            if ($id === null) {
                $this->warn("Filière map: no taxonomy branch named \"{$branch}\".");
                continue;
            }
            $this->filiereMap[$this->key($filiere)] = $id;
        }
    }

    /**
     * Place an artisan in the official taxonomy, preferring the most specific
     * node available: their exact métier, then a métier the source truncated,
     * then their corps de métier, then their filière.
     *
     * @return array{0:?int,1:string} industry id and which rung matched
     */
    private function resolveTrade(array $row): array
    {
        $metier = $this->key($row[self::COL_METIER] ?? '');
        $corps  = $this->key($row[self::COL_CORPS] ?? '');
        $fil    = $this->key($row[self::COL_FILIERE] ?? '');

        if ($metier !== '' && isset($this->byLevel[4][$metier])) {
            return [$this->byLevel[4][$metier], 'exact'];
        }

        // The source states the trade more briefly than the nomenclature does:
        // "Fondeur" for "Fondeur (Bronzier)", and long names arrive truncated
        // mid-phrase. Match on prefix, but only when the candidate continues at
        // a word boundary and exactly one candidate qualifies — "Sculpteur"
        // must not silently land on whichever "Sculpteur…" the loop met first.
        if (mb_strlen($metier) >= 5) {
            $hits = [];
            foreach ($this->byLevel[4] as $name => $id) {
                if ($name === $metier || (str_starts_with($name, $metier . ' ') && $name !== $metier)) {
                    $hits[$id] = true;
                }
            }
            if (count($hits) === 1) {
                return [array_key_first($hits), 'prefix'];
            }
        }

        // The nomenclature lists trades in the masculine; artisans write their
        // own in the feminine. "Tricoteuse", "Couturière", "Productrice" and
        // "Transformatrice" are the same trades as the entries we hold.
        foreach ($this->masculineForms($metier) as $candidate) {
            if (isset($this->byLevel[4][$candidate])) {
                return [$this->byLevel[4][$candidate], 'prefix'];
            }
        }

        if ($corps !== '' && isset($this->byLevel[3][$corps])) {
            return [$this->byLevel[3][$corps], 'corps'];
        }

        foreach ([2, 1] as $level) {
            foreach ([$fil, $corps] as $candidate) {
                if ($candidate !== '' && isset($this->byLevel[$level][$candidate])) {
                    return [$this->byLevel[$level][$candidate], 'filiere'];
                }
            }
        }

        // Last resort: the artisan's SIARC filière. Those sixteen categories are
        // the competition's own vocabulary and share no wording with the
        // official nomenclature, so they are mapped by hand. Without this, the
        // 32 artisans whose métier cell is blank have nowhere to sit at all.
        if ($fil !== '') {
            if (isset($this->filiereMap[$fil])) {
                return [$this->filiereMap[$fil], 'filiere'];
            }
            // The export truncates this column at 31 characters, so
            // "Bijouterie et Accessoire de mode" arrives as "…de mod".
            foreach ($this->filiereMap as $known => $id) {
                if (str_starts_with($known, $fil) || str_starts_with($fil, $known)) {
                    return [$id, 'filiere'];
                }
            }
        }

        return [null, 'unplaced'];
    }

    /**
     * Candidate masculine spellings for a feminine trade name, since the
     * nomenclature only lists the masculine.
     *
     * @return list<string>
     */
    private function masculineForms(string $k): array
    {
        if ($k === '') {
            return [];
        }

        $rules = [
            '/euse$/'   => 'eur',      // tricoteuse  -> tricoteur
            '/iere$/'   => 'ier',      // couturiere  -> couturier
            '/trice$/'  => 'teur',     // productrice -> producteur
            '/ienne$/'  => 'ien',      // cosmeticienne -> cosmeticien
            '/e$/'      => '',         // couturiere handled above; catches plain trailing e
        ];

        $out = [];
        foreach ($rules as $pattern => $replacement) {
            $candidate = preg_replace($pattern, $replacement, $k);
            if ($candidate !== $k) {
                $out[] = $candidate;
            }
        }

        return array_values(array_unique($out));
    }

    /** Cameroon mobile numbers arrive as 9 local digits; store them E.164. */
    private function normalisePhone(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if ($d === '') {
            return null;
        }
        $d = preg_replace('/^237/', '', $d);
        return strlen($d) === 9 ? '+237' . $d : null;
    }

    private function ownershipType(array $row): string
    {
        if ($this->key($row[self::COL_SEX] ?? '') === 'femme') {
            return 'women_owned';
        }
        if (str_contains($this->key($row[self::COL_AGEBAND] ?? ''), 'moins de 30')) {
            return 'youth_owned';
        }
        return 'private';
    }

    /** Métiers we could not place, kept so the report can name them. */
    private array $unplacedNames = [];

    private function resolveOnly(array $row): void
    {
        [, $how] = $this->resolveTrade($row);
        $this->tally[$how]++;

        if ($how === 'unplaced') {
            $label = trim($row[self::COL_METIER] ?? '') ?: '(blank métier)';
            $this->unplacedNames[$label] = ($this->unplacedNames[$label] ?? 0) + 1;
        }
        $this->normalisePhone($row[self::COL_PHONE] ?? null)
            ? $this->tally['phone_set']++
            : $this->tally['phone_skipped']++;
    }

    private function importOne(array $row): void
    {
        $code = trim($row[self::COL_CODE] ?? '');
        $name = trim($row[self::COL_NAME] ?? '');
        if ($code === '' || $name === '') {
            return;
        }

        [$industryId, $how] = $this->resolveTrade($row);
        $this->tally[$how]++;

        $existing = Business::where('siarc_code', $code)->first();

        // A claimed profile belongs to its artisan now; re-running the import
        // must not reach back into it.
        if ($existing && $existing->claimed_at) {
            $this->tally['updated']++;
            return;
        }

        // The phone belongs on the shop record, never on the placeholder user.
        // users.phone is unique, so writing it there would reserve the artisan's
        // own number against an account they cannot access — and they would then
        // be refused at signup for "phone already taken". It also makes the
        // three numbers that repeat in the source a non-issue.
        $phone = $this->normalisePhone($row[self::COL_PHONE] ?? null);
        $phone ? $this->tally['phone_set']++ : $this->tally['phone_skipped']++;

        $user = $existing
            ? User::find($existing->user_id)
            : null;

        if (! $user) {
            $user = User::create([
                'name'     => $name,
                'email'    => null,   // none exist in the source, and none may be invented
                'phone'    => null,   // kept on the business, never here — see above
                'password' => Hash::make(Str::random(40)), // unusable until the artisan claims
                'status'   => 'active',
                'account_type' => 'artisan',
                'language_preference' => 'fr',
                'is_email_verified'   => 0,
                'is_phone_verified'   => 0,
            ]);

            $roleId = DB::table('roles')->where('name', 'business_owner')->where('guard_name', 'sanctum')->value('id');
            if ($roleId) {
                DB::table('model_has_roles')->updateOrInsert([
                    'role_id' => $roleId, 'model_type' => User::class, 'model_id' => $user->id,
                ], []);
            }
        } else {
            $user->update(['name' => $name]);
        }

        $attributes = [
            'user_id'       => $user->id,
            'industry_id'   => $industryId,
            'region_id'     => $this->regions[$this->key($row[self::COL_REGION] ?? '')] ?? null,
            'city_id'       => $this->cities[$this->key($row[self::COL_COMMUNE] ?? '')] ?? null,
            'name_fr'       => $name,
            'phone'         => $phone,
            'description_fr' => $this->description($row),
            'ownership_type' => $this->ownershipType($row),
            'vendor_type'   => 'artisan',
            'status'        => 'draft',      // never public until the artisan says so
            'source_metier' => trim($row[self::COL_METIER] ?? '') ?: null,
        ];

        if ($existing) {
            $existing->update($attributes);
            $this->tally['updated']++;
            return;
        }

        Business::create($attributes + [
            'uuid'       => (string) Str::uuid(),
            'slug'       => $this->uniqueSlug($name, $code),
            'siarc_code' => $code,
        ]);
        $this->tally['created']++;
    }

    private function description(array $row): string
    {
        $bits = [];
        if ($m = trim($row[self::COL_METIER] ?? '')) {
            $bits[] = $m;
        }
        if ($c = trim($row[self::COL_COMMUNE] ?? '')) {
            $bits[] = $c;
        }
        $line = $bits ? implode(' — ', $bits) . '.' : '';

        if ($objet = trim($row[self::COL_OBJET] ?? '')) {
            $line .= ' Pièce présentée au SIARC 2026 : ' . $objet . '.';
        }
        if ($reg = trim($row[self::COL_REGISTRY] ?? '')) {
            $line .= ' Enregistrement communal : ' . $reg . '.';
        }

        return trim($line);
    }

    private function uniqueSlug(string $name, string $code): string
    {
        $base = Str::slug($name) ?: 'artisan';
        return Business::where('slug', $base)->exists()
            ? $base . '-' . Str::slug($code)
            : $base;
    }

    private function report(bool $dry): void
    {
        $t = $this->tally;
        $placed = $t['exact'] + $t['prefix'] + $t['corps'] + $t['filiere'];

        $this->table(['Trade resolution', 'Artisans'], [
            ['exact métier',        $t['exact']],
            ['métier (prefix)',     $t['prefix']],
            ['corps de métier',     $t['corps']],
            ['filière',             $t['filiere']],
            ['unplaced',            $t['unplaced']],
            ['placed total',        $placed],
        ]);

        $this->table(['Contact', 'Artisans'], [
            ['phone stored',  $t['phone_set']],
            ['no usable phone', $t['phone_skipped']],
            ['email stored',  0],
        ]);

        if ($this->unplacedNames) {
            arsort($this->unplacedNames);
            $this->warn('Métiers with no home in the taxonomy:');
            foreach (array_slice($this->unplacedNames, 0, 15, true) as $name => $n) {
                $this->line(sprintf('   %3d  %s', $n, mb_substr($name, 0, 62)));
            }
        }

        if ($dry) {
            $this->warn('Dry run — nothing was written.');
            return;
        }

        $this->info(sprintf('Created %d, updated %d.', $t['created'], $t['updated']));
        $this->line('All profiles are drafts. No email was sent: the source has no addresses, every user was written with a null email, and the mailer was faked for this run.');
    }
}
