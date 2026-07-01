<?php
/**
 * BusinessList.co.cm scraper
 * --------------------------
 * Scrapes the public business directory at https://www.businesslist.co.cm
 * for a given location (default: douala) and writes CSV + JSON.
 *
 * Dependency-free: uses built-in cURL + DOMDocument/DOMXPath only.
 * Run on your Laragon PHP stack:
 *
 *   php scripts/scrape_businesslist.php
 *   php scripts/scrape_businesslist.php --location=yaounde --max-pages=10 --delay=2
 *   php scripts/scrape_businesslist.php --location=douala --out=storage/app/scrape
 *
 * Options:
 *   --location=<slug>   Location slug as it appears in the URL (default: douala)
 *   --max-pages=<n>     Stop after N pages (default: 0 = all pages)
 *   --delay=<seconds>   Polite delay between requests (default: 2, supports decimals)
 *   --out=<dir>         Output directory (default: storage/app/scrape)
 *   --start-page=<n>    Page to start from, useful to resume (default: 1)
 *   --no-enrich         Skip visiting each company profile (listing data only)
 *   --all-cities        Scrape EVERY city/town on the platform (auto-discovered
 *                       from /browse-business-cities). Output is one combined,
 *                       globally-deduped file. Overrides --location.
 *   --cities=a,b,c      Scrape a specific comma-separated set of city slugs.
 *
 * By default the script ALSO visits each company profile page to collect
 * extra fields (mobile phone, fax, website, working hours, manager, employees,
 * registration code, categories). This is one extra request per company, so a
 * full location can take a while. Use --no-enrich for a fast listing-only run.
 *
 * Notes:
 *   - robots.txt permits /location/ paths. We send a real User-Agent and a
 *     conservative delay. Be respectful; do not lower the delay aggressively.
 *   - The site shows ~20 listings per page. Scraping stops automatically when
 *     a page yields zero listings.
 */

const BASE_URL = 'https://www.businesslist.co.cm';
const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
    . '(KHTML, like Gecko) Chrome/124.0 Safari/537.36';

/* ----------------------------- arg parsing ------------------------------ */

$opts = [
    'location'   => 'douala',
    'max-pages'  => 0,
    'delay'      => 2.0,
    'out'        => __DIR__ . '/../storage/app/scrape',
    'start-page' => 1,
    'no-enrich'  => false,
    'all-cities' => false,
    'cities'     => '',
];

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--no-enrich') {
        $opts['no-enrich'] = true;
        continue;
    }
    if ($arg === '--all-cities') {
        $opts['all-cities'] = true;
        continue;
    }
    if (preg_match('/^--([a-z\-]+)=(.*)$/', $arg, $m)) {
        $key = $m[1];
        if (array_key_exists($key, $opts)) {
            $opts[$key] = is_numeric($m[2]) ? $m[2] + 0 : $m[2];
        } else {
            fwrite(STDERR, "Unknown option: --{$key}\n");
            exit(1);
        }
    } else {
        fwrite(STDERR, "Unknown argument: {$arg}\n");
        exit(1);
    }
}

$location  = preg_replace('/[^a-z0-9\-]/i', '', (string) $opts['location']);
$maxPages  = (int) $opts['max-pages'];
$delay     = (float) $opts['delay'];
$startPage = max(1, (int) $opts['start-page']);
$outDir    = rtrim((string) $opts['out'], '/\\');
$enrich    = !$opts['no-enrich'];

if ($location === '') {
    fwrite(STDERR, "Invalid --location\n");
    exit(1);
}

if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Could not create output dir: {$outDir}\n");
    exit(1);
}

/* ------------------------------- helpers -------------------------------- */

/**
 * Fetch a URL with retries. Returns HTML string or null on failure.
 */
function fetchUrl(string $url, int $retries = 3): ?string
{
    for ($attempt = 1; $attempt <= $retries; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => USER_AGENT,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_ENCODING       => '', // accept gzip
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9,fr;q=0.8',
            ],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body !== false && $code === 200) {
            return $body;
        }

        fwrite(STDERR, "  [attempt {$attempt}/{$retries}] {$url} -> HTTP {$code} {$err}\n");
        sleep($attempt * 2); // backoff
    }

    return null;
}

/** Get trimmed text of the first node matching an XPath, or '' . */
function xpathText(DOMXPath $xp, DOMNode $ctx, string $query): string
{
    $nodes = $xp->query($query, $ctx);
    if ($nodes && $nodes->length > 0) {
        return trim(preg_replace('/\s+/u', ' ', $nodes->item(0)->textContent));
    }
    return '';
}

/** Get an attribute of the first node matching an XPath, or '' . */
function xpathAttr(DOMXPath $xp, DOMNode $ctx, string $query, string $attr): string
{
    $nodes = $xp->query($query, $ctx);
    if ($nodes && $nodes->length > 0 && $nodes->item(0) instanceof DOMElement) {
        return trim($nodes->item(0)->getAttribute($attr));
    }
    return '';
}

/**
 * Parse one results page into an array of company records.
 */
function parsePage(string $html): array
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();

    $xp = new DOMXPath($dom);
    $companies = $xp->query("//div[contains(@class,'company') and @data-cmpid]");

    $rows = [];
    foreach ($companies as $node) {
        if (!$node instanceof DOMElement) {
            continue;
        }
        $id = trim($node->getAttribute('data-cmpid'));
        if ($id === '' || !ctype_digit($id)) {
            continue;
        }

        $name    = xpathText($xp, $node, ".//div[contains(@class,'company_header')]//h3/a");
        $relUrl  = xpathAttr($xp, $node, ".//div[contains(@class,'company_header')]//h3/a", 'href');
        $address = xpathText($xp, $node, ".//div[contains(@class,'address')]");

        $phone = xpathText(
            $xp,
            $node,
            ".//div[contains(@class,'s')][.//i[contains(@aria-label,'Phone')]]//span"
        );

        $established = xpathText(
            $xp,
            $node,
            ".//div[contains(@class,'s')][.//i[contains(@aria-label,'Calendar')]]//span"
        );
        // "2014 Established" -> "2014"
        if (preg_match('/\b(\d{4})\b/', $established, $m)) {
            $established = $m[1];
        }

        $yearsWithUs = xpathText($xp, $node, ".//u[contains(@class,'v4')]/b");

        $verifiedNodes = $xp->query(
            ".//u[contains(@class,'v')][.//i[contains(@aria-label,'Verified')]]",
            $node
        );
        $verified = $verifiedNodes && $verifiedNodes->length > 0;

        $isSponsored = $xp->query(".//i[normalize-space(.)='Sponsored']", $node)->length > 0;

        $lat = xpathAttr($xp, $node, ".//div[contains(@class,'mapmarker')]", 'data-ltd');
        $lng = xpathAttr($xp, $node, ".//div[contains(@class,'mapmarker')]", 'data-lng');

        $logo = xpathAttr($xp, $node, ".//a[contains(@class,'logo')]", 'data-bg');

        $rows[] = [
            'id'            => $id,
            'name'          => html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'profile_url'   => $relUrl ? BASE_URL . $relUrl : '',
            'address'       => html_entity_decode($address, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'phone'         => $phone,
            'established'   => $established,
            'years_with_us' => $yearsWithUs,
            'verified'      => $verified ? 'yes' : 'no',
            'sponsored'     => $isSponsored ? 'yes' : 'no',
            'latitude'      => $lat,
            'longitude'     => $lng,
            'logo_url'      => $logo ? BASE_URL . $logo : '',
            // filled in by enrichProfile() (profile page), blank otherwise:
            'mobile_phone'      => '',
            'fax'               => '',
            'website'           => '',
            'manager'           => '',
            'employees'         => '',
            'registration_code' => '',
            'working_hours'     => '',
            'categories'        => '',
        ];
    }

    return $rows;
}

/**
 * Parse a company profile page into extra fields (everything except the
 * company description). Returns an associative array of the enrichment columns.
 */
function parseProfile(string $html): array
{
    $out = [
        'mobile_phone'      => '',
        'fax'               => '',
        'website'           => '',
        'manager'           => '',
        'employees'         => '',
        'registration_code' => '',
        'working_hours'     => '',
        'categories'        => '',
    ];

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    $xp = new DOMXPath($dom);

    $decode = static fn(string $s): string =>
        trim(preg_replace('/\s+/u', ' ', html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

    // Build a label => value map from every <div class="info"> block.
    foreach ($xp->query("//div[contains(concat(' ', @class, ' '), ' info ')]") as $info) {
        if (!$info instanceof DOMElement) {
            continue;
        }
        $labelNodes = $xp->query("./div[@class='label']", $info);
        if (!$labelNodes || $labelNodes->length === 0) {
            continue;
        }
        $label = $decode($labelNodes->item(0)->textContent);
        $full  = $decode($info->textContent);
        // value = the info block's text minus its own label prefix
        $value = $decode((string) preg_replace('/^' . preg_quote($label, '/') . '/u', '', $full));

        switch (mb_strtolower($label)) {
            case 'mobile phone':
                $out['mobile_phone'] = $value;
                break;
            case 'fax':
                $out['fax'] = $value;
                break;
            case 'company manager':
                $out['manager'] = $value;
                break;
            case 'employees':
                $out['employees'] = $value;
                break;
            case 'registration code':
                $out['registration_code'] = $value;
                break;
        }
    }

    // Website: prefer the real href over the displayed text.
    $web = xpathAttr($xp, $dom->documentElement, "//div[contains(@class,'weblinks')]//a", 'href');
    if ($web === '') {
        $web = xpathText($xp, $dom->documentElement, "//div[contains(@class,'weblinks')]//a");
    }
    $out['website'] = $web;

    // Working hours: one entry per day -> "Monday: 8:00 am - 5:00 pm; ...".
    $hours = [];
    foreach ($xp->query("//div[@id='open_hours']//ul/li") as $li) {
        $hours[] = $decode($li->textContent);
    }
    $out['working_hours'] = implode('; ', $hours);

    // Categories: only genuine /category/ links (skip the /companies/ tag spam).
    $cats = [];
    foreach ($xp->query("//div[@class='tags']/a[starts-with(@href,'/category/')]
                         | //div[@class='tags']//details//a[starts-with(@href,'/category/')]") as $a) {
        $name = $decode($a->textContent);
        if ($name !== '') {
            $cats[$name] = true; // dedupe
        }
    }
    $out['categories'] = implode('; ', array_keys($cats));

    return $out;
}

/**
 * Discover every city/town slug from the platform's by-location index.
 * Returns an array of slugs (e.g. ['abo', 'douala', 'yaounde', ...]).
 */
function discoverCities(): array
{
    $html = fetchUrl(BASE_URL . '/browse-business-cities');
    if ($html === null) {
        fwrite(STDERR, "Could not load /browse-business-cities to discover cities.\n");
        return [];
    }
    if (!preg_match_all('#href="/location/([a-z0-9\-]+)"#', $html, $m)) {
        return [];
    }
    return array_values(array_unique($m[1]));
}

/**
 * Scrape all listing pages for one location into $all (by reference),
 * deduping globally via $seen. Returns the count of new companies added.
 */
function scrapeLocation(
    string $location,
    int $maxPages,
    int $startPage,
    float $delay,
    array &$all,
    array &$seen
): int {
    $page  = $startPage;
    $added = 0;

    while (true) {
        $url = $page === 1
            ? BASE_URL . "/location/{$location}"
            : BASE_URL . "/location/{$location}/{$page}";

        fwrite(STDOUT, "  [{$location}] page {$page}: {$url}\n");

        $html = fetchUrl($url);
        if ($html === null) {
            fwrite(STDERR, "    Failed to fetch page {$page}, stopping this city.\n");
            break;
        }

        $rows = parsePage($html);
        if (count($rows) === 0) {
            fwrite(STDOUT, "    End of {$location} (empty page).\n");
            break;
        }

        $new       = 0;
        $pageDupes  = 0;
        foreach ($rows as $row) {
            if (isset($seen[$row['id']])) {
                $pageDupes++;
                continue; // dedupe across pages AND across cities
            }
            $seen[$row['id']] = true;
            $row['source_city'] = $location;
            $all[] = $row;
            $new++;
            $added++;
        }

        fwrite(STDOUT, "    +{$new} new (grand total: " . count($all) . ")\n");

        // Small cities re-serve page 1 for out-of-range page numbers, so an
        // all-duplicate page means we've exhausted this city — stop paging.
        if ($new === 0 && $pageDupes > 0) {
            fwrite(STDOUT, "    End of {$location} (all duplicates).\n");
            break;
        }

        if ($maxPages > 0 && $page >= $startPage + $maxPages - 1) {
            fwrite(STDOUT, "    Reached --max-pages limit.\n");
            break;
        }

        $page++;
        if ($delay > 0) {
            usleep((int) round($delay * 1_000_000));
        }
    }

    return $added;
}

/* -------------------------------- main ---------------------------------- */

// Build the list of cities to scrape.
if ($opts['all-cities']) {
    fwrite(STDOUT, "Discovering all cities from /browse-business-cities ...\n");
    $locations = discoverCities();
    if (count($locations) === 0) {
        fwrite(STDERR, "No cities discovered. Aborting.\n");
        exit(1);
    }
    $label = 'cameroon_all';
    fwrite(STDOUT, "Discovered " . count($locations) . " cities.\n");
} elseif (trim((string) $opts['cities']) !== '') {
    $locations = array_values(array_filter(array_map(
        static fn($s) => preg_replace('/[^a-z0-9\-]/i', '', trim($s)),
        explode(',', (string) $opts['cities'])
    )));
    $label = count($locations) === 1 ? $locations[0] : 'cities_' . count($locations);
} else {
    $locations = [$location];
    $label = $location;
}

fwrite(STDOUT, "Scraping businesslist.co.cm — "
    . count($locations) . " location(s): " . implode(', ', $locations) . "\n");
fwrite(STDOUT, "Output dir: {$outDir} | delay: {$delay}s | max-pages: "
    . ($maxPages ?: 'all') . " | enrich: " . ($enrich ? 'yes' : 'no') . "\n\n");

// Crash-safe cache files (stable names, not timestamped) so an interrupted
// run resumes instead of starting over. Delete them to force a fresh scrape.
$listingsCache  = "{$outDir}/_cache_{$label}_listings.json";
$enrichedNdjson = "{$outDir}/_cache_{$label}_enriched.ndjson";

/* --------------------------- phase 1: listings -------------------------- */

$all  = [];
$seen = [];

if (file_exists($listingsCache)) {
    $cached = json_decode((string) file_get_contents($listingsCache), true);
    if (is_array($cached) && count($cached) > 0) {
        $all = $cached;
        fwrite(STDOUT, "Resuming: loaded " . count($all)
            . " cached listings from {$listingsCache}\n\n");
    }
}

if (count($all) === 0) {
    foreach ($locations as $ci => $loc) {
        fwrite(STDOUT, "City " . ($ci + 1) . "/" . count($locations) . ": {$loc}\n");
        scrapeLocation($loc, $maxPages, $startPage, $delay, $all, $seen);
        if ($delay > 0 && $ci < count($locations) - 1) {
            usleep((int) round($delay * 1_000_000));
        }
    }
    // Persist listings immediately so the (long) enrichment phase is resumable
    // even if it never finishes.
    file_put_contents(
        $listingsCache,
        json_encode($all, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
    fwrite(STDOUT, "\nListings phase complete: " . count($all)
        . " companies cached to {$listingsCache}\n");
}

if (count($all) === 0) {
    fwrite(STDERR, "\nNo records scraped.\n");
    exit(1);
}

/* -------------------- phase 2: enrichment (resumable) ------------------- */

if ($enrich) {
    // Load any already-enriched records from a prior (interrupted) run.
    $done = [];
    if (file_exists($enrichedNdjson)) {
        foreach (file($enrichedNdjson, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $rec = json_decode($line, true);
            if (is_array($rec) && isset($rec['id'])) {
                $done[$rec['id']] = $rec;
            }
        }
        fwrite(STDOUT, "Resuming enrichment: " . count($done)
            . " profiles already done.\n");
    }

    $total = count($all);
    fwrite(STDOUT, "Enriching {$total} profiles (mobile, fax, website, hours, "
        . "manager, employees, reg. code, categories)...\n");

    $fh = fopen($enrichedNdjson, 'a'); // append; each profile flushed immediately
    foreach ($all as $i => $row) {
        $n  = $i + 1;
        $id = $row['id'];

        if (isset($done[$id])) {
            $all[$i] = $done[$id]; // already enriched in a previous run
            continue;
        }

        $rec     = $row;
        $fetched = false;
        if ($row['profile_url'] !== '') {
            $html = fetchUrl($row['profile_url']);
            $fetched = true;
            if ($html === null) {
                fwrite(STDERR, "  [{$n}/{$total}] failed: {$row['profile_url']}\n");
            } else {
                $rec = array_merge($row, parseProfile($html));
                fwrite(STDOUT, "  [{$n}/{$total}] {$row['name']}\n");
            }
        }

        $all[$i]    = $rec;
        $done[$id]  = $rec;
        fwrite($fh, json_encode($rec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
        fflush($fh); // persist every profile so a crash loses at most one

        if ($fetched && $delay > 0 && $n < $total) {
            usleep((int) round($delay * 1_000_000));
        }
    }
    fclose($fh);
}

/* ------------------------------ write out ------------------------------- */

$stamp    = date('Ymd_His');
$jsonPath = "{$outDir}/businesslist_{$label}_{$stamp}.json";
$csvPath  = "{$outDir}/businesslist_{$label}_{$stamp}.csv";

file_put_contents(
    $jsonPath,
    json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

$fh = fopen($csvPath, 'w');
fprintf($fh, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
fputcsv($fh, array_keys($all[0]));
foreach ($all as $row) {
    fputcsv($fh, $row);
}
fclose($fh);

// Run finished cleanly — drop the resume caches so the next run starts fresh.
@unlink($listingsCache);
@unlink($enrichedNdjson);

fwrite(STDOUT, "\nDone. " . count($all) . " companies scraped.\n");
fwrite(STDOUT, "  JSON: {$jsonPath}\n");
fwrite(STDOUT, "  CSV:  {$csvPath}\n");
