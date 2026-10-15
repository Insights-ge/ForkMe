<?php

namespace App\Data;

readonly class ImportRowErrorData
{
    /**
     * @param  ?int  $rowNumber  Null for errors about the whole file, such as missing headers.
     * @param  ?string  $column  The spreadsheet header the error is about, if any.
     */
    public function __construct(
        public ?int $rowNumber,
        public ?string $column,
        public string $message,
    ) {}
}
