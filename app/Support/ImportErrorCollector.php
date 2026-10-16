<?php

namespace App\Support;

use App\Data\ImportErrorListData;
use App\Data\ImportRowErrorData;

/**
 * Collects import errors, keeping only the first ones while still counting all of them.
 */
class ImportErrorCollector
{
    /**
     * @var list<ImportRowErrorData>
     */
    private array $errors = [];

    private int $totalCount = 0;

    public function __construct(private readonly int $maxKeptErrors = ImportErrorListData::MAX_KEPT_ERRORS) {}

    public function add(?int $rowNumber, ?string $column, string $message): void
    {
        $this->totalCount++;

        if (count($this->errors) < $this->maxKeptErrors) {
            $this->errors[] = new ImportRowErrorData($rowNumber, $column, $message);
        }
    }

    public function toData(): ImportErrorListData
    {
        return new ImportErrorListData($this->errors, $this->totalCount);
    }
}
