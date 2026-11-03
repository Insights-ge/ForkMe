<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/** Each record represents an incompatible component and infill pair. */
#[Fillable(['component_id', 'infill_id'])]
class ComponentInfill extends Pivot
{
    protected $table = 'component_infill';

    /** @return BelongsTo<Component, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    /** @return BelongsTo<Infill, $this> */
    public function infill(): BelongsTo
    {
        return $this->belongsTo(Infill::class);
    }
}
