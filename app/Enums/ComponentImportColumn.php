<?php

namespace App\Enums;

/**
 * The header row of the component import spreadsheet.
 */
enum ComponentImportColumn: string
{
    case Type = 'type';
    case UniqueCode = 'unique_code';
    case Code = 'code';
    case Description = 'description';
    case Color = 'color';
}
