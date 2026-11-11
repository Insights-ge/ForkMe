<?php

namespace App\Data;

readonly class BuilderInfillVisualData
{
    public function __construct(
        public int $infillId,
        public BuilderVisualData $visual,
    ) {}
}
