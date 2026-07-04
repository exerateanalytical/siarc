<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the 8 export rows shown on the "Data Export Centre" admin design
 * (Data Export Centre.png) verbatim: names, record counts, formats, statuses,
 * sizes, creation and expiration timestamps. Idempotent (keyed by name).
 */
class DesignDataExportsSeeder extends Seeder
{
    public function run(): void
    {
        $mb = 1024 * 1024;
        // [sort, name, dataset, format, status, records, counts_files, size_bytes, created, expires]
        $rows = [
            [1, 'Artisans_Complete_2025-05-12',      'artisans',      'csv',  'reussi',   258,   false, (int) (245.8 * $mb), '2025-05-12 10:30:00', '2025-05-19 10:30:00'],
            [2, 'Produits_Services_2025-05-12',      'produits',      'xlsx', 'reussi',   1245,  false, (int) (182.4 * $mb), '2025-05-12 09:15:00', '2025-05-19 09:15:00'],
            [3, 'Users_Activity_Report_2025-05-11',  'utilisateurs',  'csv',  'en_cours', 12458, false, null,                '2025-05-11 16:45:00', null],
            [4, 'Transactions_Financial_2025-05-10', 'transactions',  'pdf',  'reussi',   5678,  false, (int) (75.3 * $mb),  '2025-05-10 14:20:00', '2025-05-17 14:20:00'],
            [5, 'KYC_Verifications_2025-05-09',      'kyc',           'csv',  'echoue',   842,   false, null,                '2025-05-09 11:05:00', null],
            [6, 'Sales_Report_Monthly_2025-05-08',   'rapports',      'xlsx', 'reussi',   3256,  false, (int) (63.7 * $mb),  '2025-05-08 17:30:00', '2025-05-15 17:30:00'],
            [7, 'Media_Library_2025-05-07',          'medias',        'zip',  'reussi',   8924,  true,  (int) (2.45 * 1024 * $mb), '2025-05-07 10:10:00', '2025-05-15 10:10:00'],
            [8, 'Events_And_News_2025-05-06',        'evenements',    'csv',  'reussi',   156,   false, (int) (12.6 * $mb),  '2025-05-06 09:40:00', '2025-05-13 09:40:00'],
        ];

        foreach ($rows as [$sort, $name, $dataset, $format, $status, $records, $countsFiles, $size, $created, $expires]) {
            DB::table('data_exports')->updateOrInsert(
                ['name' => $name],
                [
                    'dataset'      => $dataset,
                    'format'       => $format,
                    'status'       => $status,
                    'records'      => $records,
                    'counts_files' => $countsFiles,
                    'size_bytes'   => $size,
                    'expires_at'   => $expires,
                    'sort_order'   => $sort,
                    'created_at'   => $created,
                    'updated_at'   => $created,
                ]
            );
        }

        // Fill the registry up to the design's 124 exports (16 pages at 8/page)
        // with plausible historical rows so the pagination is fully real.
        $existing = DB::table('data_exports')->count();
        if ($existing < 124) {
            $datasets = ['artisans', 'produits', 'utilisateurs', 'transactions', 'kyc', 'rapports', 'medias', 'evenements'];
            $formats  = ['csv', 'csv', 'xlsx', 'csv', 'pdf', 'xlsx', 'zip', 'csv'];
            $need = 124 - $existing;
            $insert = [];
            for ($i = 0; $i < $need; $i++) {
                $dataset = $datasets[$i % 8];
                $format  = $formats[$i % 8];
                // réussi-dominant mix, deterministic
                $status  = match (true) { $i % 29 === 7 => 'en_cours', $i % 23 === 5 => 'echoue', $i % 11 === 3 => 'planifie', default => 'reussi' };
                $created = \Carbon\Carbon::parse('2025-05-05 18:00:00')->subHours(7 + $i * 26)->subMinutes(($i * 17) % 60);
                $done    = $status === 'reussi';
                $insert[] = [
                    'name'         => ucfirst($dataset) . '_Export_' . $created->format('Y-m-d') . '_' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'dataset'      => $dataset,
                    'format'       => $format,
                    'status'       => $status,
                    'records'      => 120 + ($i * 37) % 4200,
                    'counts_files' => $dataset === 'medias',
                    'size_bytes'   => $done ? (2 + ($i * 13) % 220) * 1048576 : null,
                    'expires_at'   => $done ? $created->copy()->addDays(7) : null,
                    'sort_order'   => null,
                    'created_at'   => $created,
                    'updated_at'   => $created,
                ];
            }
            foreach (array_chunk($insert, 50) as $chunk) DB::table('data_exports')->insert($chunk);
            $this->command?->info('Filler exports seeded: ' . $need);
        }

        $this->command?->info('Design data exports seeded: ' . count($rows));
    }
}
