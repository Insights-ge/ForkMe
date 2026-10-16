<?php

namespace App\Data;

readonly class ImportErrorListData
{
    public const int MAX_KEPT_ERRORS = 200;

    /**
     * @param  list<ImportRowErrorData>  $errors  The first errors only (at most MAX_KEPT_ERRORS); see $totalCount for how many there are.
     */
    public function __construct(
        public array $errors,
        public int $totalCount,
    ) {}

    public function hasErrors(): bool
    {
        return $this->totalCount > 0;
    }
}
