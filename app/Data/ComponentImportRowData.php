<?php

namespace App\Data;

/**
 * One spreadsheet row, with trimmed and not yet validated values.
 */
readonly class ComponentImportRowData
{
    /**
     * @param  int  $rowNumber  The row number as shown in Excel (the header is row 1).
     * @param  string  $type  The Georgian type name, e.g. "ტიხარი".
     * @param  string  $color  The Georgian colour name, or empty.
     */
    public function __construct(
        public int $rowNumber,
        public string $type,
        public string $uniqueCode,
        public string $code,
        public string $description,
        public string $color,
    ) {}
}
