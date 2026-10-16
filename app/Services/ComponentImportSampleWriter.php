<?php

namespace App\Services;

use App\Enums\ComponentImportColumn;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Writes the component import sample: an .xlsx file with only the header row.
 */
class ComponentImportSampleWriter
{
    /**
     * @param  string  $path  A file path, or "php://output" to stream it.
     */
    public function write(string $path): void
    {
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(array_column(ComponentImportColumn::cases(), 'value')));
        $writer->close();
    }
}
