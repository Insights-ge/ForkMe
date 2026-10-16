<?php

namespace App\Data;

/**
 * The rows read from an uploaded component spreadsheet, or the errors that prevented reading them.
 */
readonly class ComponentSpreadsheetData
{
    /**
     * @param  list<ComponentImportRowData>  $rows
     * @param  list<ImportRowErrorData>  $errors  Errors about the whole file (unreadable file, wrong headers, too many rows).
     */
    public function __construct(
        public array $rows,
        public array $errors,
    ) {}

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
