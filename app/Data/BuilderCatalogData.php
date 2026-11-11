<?php

namespace App\Data;

readonly class BuilderCatalogData
{
    /**
     * @param  list<BuilderDoorModelData>  $models
     * @param  list<BuilderInfillData>  $infills
     */
    public function __construct(
        public array $models,
        public array $infills,
    ) {}
}
