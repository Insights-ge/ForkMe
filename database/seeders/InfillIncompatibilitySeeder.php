<?php

namespace Database\Seeders;

use App\Enums\ComponentType;
use App\Models\Component;
use App\Models\Infill;
use Illuminate\Database\Seeder;
use RuntimeException;

class InfillIncompatibilitySeeder extends Seeder
{
    /** @var list<string> */
    private const INFILL_CODES = [
        'მინ0601', 'მინ1601', 'მინ0602', 'მინ1602', 'მინ0603',
        'მინ1603', 'მინ0604', 'მინ0607', 'მინ1607',
    ];

    /** @var list<string> */
    private const PROFILE_CODES = ['გალუ4111', 'გალუ4171'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = Component::query()
            ->where('type', ComponentType::SideProfile)
            ->whereIn('code', self::PROFILE_CODES)
            ->get();
        $infills = Infill::query()->whereIn('code', self::INFILL_CODES)->get();

        foreach (self::PROFILE_CODES as $code) {
            if (! $components->contains('code', $code)) {
                throw new RuntimeException("Missing side profile {$code}. Run DoorCatalogSeeder first.");
            }
        }

        foreach (self::INFILL_CODES as $code) {
            if ($infills->contains('code', $code)) {
                continue;
            }

            if ($code === 'მინ1607') {
                $this->command?->warn('Skipping მინ1607: its supplier unique code is missing from the source spreadsheet. Rerun after adding the infill.');

                continue;
            }

            throw new RuntimeException("Missing infill {$code}. Run InfillSeeder first.");
        }

        Infill::query()->getConnection()->transaction(function () use ($components, $infills): void {
            foreach ($infills as $infill) {
                $infill->incompatibleComponents()->syncWithoutDetaching($components->modelKeys());
            }
        });
    }
}
