<?php

namespace App\Data;

/**
 * One spreadsheet row, with trimmed and not yet validated values.
 */
readonly class DoorVariantImportRowData
{
    /**
     * @param  int  $rowNumber  The row number as shown in Excel (the header is row 1).
     * @param  string  $color  The Georgian colour name, e.g. "შავი".
     * @param  string  $heightRangeMin  As written in the file; the validator checks it is a whole number.
     * @param  string  $heightRangeMax  As written in the file; the validator checks it is a whole number.
     * @param  string  $partitionUniqueCode  Empty when the variant has no partition.
     */
    public function __construct(
        public int $rowNumber,
        public string $modelName,
        public string $productFullName,
        public string $productCode,
        public string $color,
        public string $frameDesignCode,
        public string $uniqueCode,
        public string $heightRangeMin,
        public string $heightRangeMax,
        public string $sideProfileUniqueCode,
        public string $topRailUniqueCode,
        public string $bottomRailUniqueCode,
        public string $partitionUniqueCode,
        public string $softCloseMechanismUniqueCode,
    ) {}
}
