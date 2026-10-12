<?php

use App\Enums\ComponentType;
use App\Models\Component;
use App\Models\DoorVariant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A component is identified by its unique code AND its type: the same profile used as a top rail
 * and as a partition is stored as two components. Variant slots that point to a component of a
 * different type are re-pointed to a copy of that component with the slot's type.
 */
return new class extends Migration
{
    /**
     * @var array<string, ComponentType>
     */
    private const SLOT_TYPES = [
        'side_profile_component_id' => ComponentType::SideProfile,
        'top_rail_component_id' => ComponentType::TopRail,
        'bottom_rail_component_id' => ComponentType::BottomRail,
        'partition_component_id' => ComponentType::Partition,
        'soft_close_mechanism_component_id' => ComponentType::SoftCloseMechanism,
    ];

    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropUnique(['unique_code']);
            $table->unique(['unique_code', 'type']);
        });

        foreach (self::SLOT_TYPES as $foreignKey => $type) {
            $this->splitMismatchedComponents($foreignKey, $type);
        }
    }

    public function down(): void
    {
        $duplicateCodes = Component::query()
            ->select('unique_code')
            ->groupBy('unique_code')
            ->havingRaw('count(*) > 1')
            ->pluck('unique_code');

        foreach ($duplicateCodes as $uniqueCode) {
            $this->mergeComponents($uniqueCode);
        }

        Schema::table('components', function (Blueprint $table) {
            $table->dropUnique(['unique_code', 'type']);
            $table->unique('unique_code');
        });
    }

    private function splitMismatchedComponents(string $foreignKey, ComponentType $type): void
    {
        $mismatchedComponents = Component::query()
            ->whereIn('id', DoorVariant::query()->select($foreignKey)->whereNotNull($foreignKey))
            ->where('type', '!=', $type)
            ->get();

        foreach ($mismatchedComponents as $component) {
            $typedComponent = Component::query()->firstOrCreate(
                ['unique_code' => $component->unique_code, 'type' => $type],
                [
                    'code' => $component->code,
                    'description' => $component->description,
                    'color' => $component->color,
                ],
            );

            DoorVariant::query()
                ->where($foreignKey, $component->id)
                ->update([$foreignKey => $typedComponent->id]);
        }
    }

    private function mergeComponents(string $uniqueCode): void
    {
        $componentIds = Component::query()->where('unique_code', $uniqueCode)->orderBy('id')->pluck('id');
        $keptId = $componentIds->shift();

        foreach (array_keys(self::SLOT_TYPES) as $foreignKey) {
            DoorVariant::query()
                ->whereIn($foreignKey, $componentIds)
                ->update([$foreignKey => $keptId]);
        }

        Component::query()->whereIn('id', $componentIds)->delete();
    }
};
