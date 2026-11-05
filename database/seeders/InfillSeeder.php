<?php

namespace Database\Seeders;

use App\Models\Infill;
use Illuminate\Database\Seeder;
use RuntimeException;

class InfillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $handle = fopen(database_path('seeders/data/infills.csv'), 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to open infills CSV.');
        }

        try {
            if (fgetcsv($handle) !== ['code', 'description', 'unique_code']) {
                throw new RuntimeException('Invalid infills CSV header.');
            }

            Infill::query()->getConnection()->transaction(function () use ($handle): void {
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) !== 3 || in_array('', $row, true) || in_array(null, $row, true)) {
                        throw new RuntimeException('Each infill requires a code, description, and supplier unique code.');
                    }

                    Infill::query()->updateOrCreate(
                        ['unique_code' => $row[2]],
                        ['code' => $row[0], 'description' => $row[1]],
                    );
                }
            });
        } finally {
            fclose($handle);
        }
    }
}
