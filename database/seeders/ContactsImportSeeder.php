<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Contact;
use App\Models\Country;
use App\Models\ContactCategory;
use App\Models\ContactSubcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ContactsImportSeeder extends Seeder
{
    private const DEFAULT_FILE = '2026.05.17_ACDF Database.xlsx';

    private const COUNTRY_ALIASES = [
        'uk'             => 'United Kingdom',
        'uk, swiss'      => 'United Kingdom',
        'usa'            => 'United States',
        'us'             => 'United States',
        'new york'       => 'United States',
        'uae'            => 'United Arab Emirates',
        'ksa'            => 'Saudi Arabia',
        'saudi arabi'    => 'Saudi Arabia',
        'kz'             => 'Kazakhstan',
        'korea'          => 'South Korea',
        'turkiye'        => 'Turkey',
        'türkiye'        => 'Turkey',
        'switerland'     => 'Switzerland',
        'swizerland'     => 'Switzerland',
        'itaila'         => 'Italy',
        'luxemburg'      => 'Luxembourg',
        'lagos'          => 'Nigeria',
        'tbilisi'        => 'Georgia',
    ];

    private array $categoryCache = [];
    private array $subcategoryCache = [];
    private array $countryCache = [];
    private array $cityCache = [];

    public function run(): void
    {
        $file = base_path(self::DEFAULT_FILE);

        if (!file_exists($file)) {
            $this->command->error("Excel file not found: {$file}");
            return;
        }

        $missing = $this->missingSchema();
        if (!empty($missing)) {
            $this->command->error('Schema is out of date — missing: ' . implode(', ', $missing));
            $this->command->error('Run "php artisan migrate" first.');
            return;
        }

        $this->command->info("Loading countries map...");
        foreach (Country::select('id', 'name')->get() as $country) {
            $this->countryCache[mb_strtolower($country->name)] = $country->id;
        }

        if (empty($this->countryCache)) {
            $this->command->warn('Countries table is empty — contacts will be imported without country links.');
        }

        $this->command->info("Reading Excel file...");
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $this->command->info("Importing " . ($highestRow - 1) . " rows...");

        $seenEmails = [];
        $batch = [];
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $now = now();

        DB::beginTransaction();

        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $values = $sheet->rangeToArray("A{$row}:V{$row}", null, true, false, false)[0];

                $prefix    = $this->str($values[0]);
                $firstname = $this->str($values[1]);
                $lastname  = $this->str($values[2]);
                $title     = $this->str($values[3]);
                $company   = $this->str($values[4]);
                $email     = $this->str($values[5]);
                $phone     = $this->str($values[7]);
                $cellphone = $this->str($values[8]);
                $address   = $this->str($values[9]);
                $address2  = $this->str($values[10]);
                $postBox   = $this->str($values[11]);
                $zipCode   = $this->str($values[12]);
                $city      = $this->str($values[13]);
                $country   = $this->str($values[14]);
                $language  = $this->str($values[16]);
                $catName   = $this->str($values[17]);
                $subName   = $this->str($values[18]);

                if ($firstname === null && $lastname === null && $company === null && $email === null) {
                    $skipped++;
                    continue;
                }

                $emailKey = $email !== null ? mb_strtolower($email) : null;
                if ($emailKey !== null && isset($seenEmails[$emailKey])) {
                    $skipped++;
                    continue;
                }
                if ($emailKey !== null) {
                    $seenEmails[$emailKey] = true;
                }

                $categoryId = $catName ? $this->resolveCategory($catName) : null;
                $subcategoryId = ($catName && $subName) ? $this->resolveSubcategory($subName, $categoryId) : null;
                $countryId = $country ? $this->resolveCountry($country) : null;
                $cityId = ($city && $countryId) ? $this->resolveCity($city, $countryId) : null;

                $attributes = [
                    'prefix'         => $prefix,
                    'firstname'      => $firstname,
                    'lastname'       => $lastname,
                    'title'          => $title,
                    'company'        => $company,
                    'phone'          => $phone,
                    'cellphone'      => $cellphone,
                    'address'        => $address,
                    'address2'       => $address2,
                    'post_box'       => $postBox,
                    'zip_code'       => $zipCode,
                    'city'           => $cityId,
                    'country'        => $countryId,
                    'language'       => $language,
                    'email'          => $email,
                    'category_id'    => $categoryId,
                    'subcategory_id' => $subcategoryId,
                    'status'         => Contact::STATUS_ACTIVE,
                    'updated_at'     => $now,
                ];

                if ($email !== null) {
                    $existing = Contact::where('email', $email)->first();
                    if ($existing) {
                        $existing->update($attributes);
                        $updated++;
                        continue;
                    }
                }

                $attributes['created_at'] = $now;
                $batch[] = $attributes;

                if (count($batch) >= 200) {
                    Contact::insert($batch);
                    $inserted += count($batch);
                    $batch = [];
                    $this->command->info("  ... inserted {$inserted}");
                }
            }

            if (!empty($batch)) {
                Contact::insert($batch);
                $inserted += count($batch);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("Import failed: " . $e->getMessage());
            throw $e;
        }

        $this->command->info("Done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}");
    }

    private function missingSchema(): array
    {
        $required = [
            'contacts' => ['firstname', 'lastname', 'company', 'email', 'category_id', 'subcategory_id', 'country'],
            'contact_categories' => ['title'],
            'contact_subcategories' => ['title', 'category_id'],
        ];

        $missing = [];
        foreach ($required as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $missing[] = "table {$table}";
                continue;
            }
            foreach ($columns as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    $missing[] = "{$table}.{$col}";
                }
            }
        }
        return $missing;
    }

    private function str($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function resolveCategory(string $name): int
    {
        $key = mb_strtolower($name);
        if (isset($this->categoryCache[$key])) {
            return $this->categoryCache[$key];
        }

        $category = ContactCategory::firstOrCreate(
            ['title' => $name],
            ['status' => ContactCategory::STATUS_ACTIVE]
        );
        return $this->categoryCache[$key] = $category->id;
    }

    private function resolveSubcategory(string $name, ?int $categoryId): ?int
    {
        if ($categoryId === null) {
            return null;
        }
        $key = $categoryId . '|' . mb_strtolower($name);
        if (isset($this->subcategoryCache[$key])) {
            return $this->subcategoryCache[$key];
        }

        $sub = ContactSubcategory::firstOrCreate(
            ['title' => $name, 'category_id' => $categoryId],
            ['status' => ContactSubcategory::STATUS_ACTIVE]
        );
        return $this->subcategoryCache[$key] = $sub->id;
    }

    private function resolveCountry(string $name): ?int
    {
        $key = mb_strtolower($name);
        if (isset($this->countryCache[$key])) {
            return $this->countryCache[$key];
        }
        if (isset(self::COUNTRY_ALIASES[$key])) {
            $aliasKey = mb_strtolower(self::COUNTRY_ALIASES[$key]);
            return $this->countryCache[$aliasKey] ?? null;
        }
        return null;
    }

    private function resolveCity(string $name, int $countryId): ?int
    {
        $key = $countryId . '|' . mb_strtolower($name);
        if (array_key_exists($key, $this->cityCache)) {
            return $this->cityCache[$key];
        }
        $id = City::where('country_id', $countryId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->value('id');
        return $this->cityCache[$key] = $id ? (int) $id : null;
    }
}
