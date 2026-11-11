<?php

namespace App\Data;

readonly class BuilderInfillData
{
    public function __construct(
        public int $id,
        public string $code,
        public string $description,
    ) {}
}
