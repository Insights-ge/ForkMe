<?php

namespace App\Actions;

use App\Data\ComponentImportPreviewData;
use App\Data\ImportErrorListData;
use App\Models\Component;
use App\Models\User;
use App\Services\ComponentImportService;
use App\Services\ComponentSpreadsheetReader;
use Illuminate\Support\Facades\Gate;

class PreviewComponentImportAction
{
    public function __construct(
        private readonly ComponentSpreadsheetReader $reader,
        private readonly ComponentImportService $importService,
    ) {}

    /**
     * Reads the spreadsheet and counts what importing it would do, without saving anything.
     *
     * @param  string  $path  The absolute path of the uploaded file.
     */
    public function execute(User $user, string $path): ComponentImportPreviewData
    {
        Gate::forUser($user)->authorize('import', Component::class);

        $spreadsheet = $this->reader->read($path);

        if ($spreadsheet->hasErrors()) {
            $errors = new ImportErrorListData(
                array_slice($spreadsheet->errors, 0, ImportErrorListData::MAX_KEPT_ERRORS),
                count($spreadsheet->errors),
            );

            return new ComponentImportPreviewData(0, 0, 0, $errors);
        }

        return $this->importService->preview($spreadsheet->rows);
    }
}
