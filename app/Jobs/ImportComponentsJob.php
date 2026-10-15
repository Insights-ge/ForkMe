<?php

namespace App\Jobs;

use App\Data\ComponentImportResultData;
use App\Data\ImportErrorListData;
use App\Data\ImportRowErrorData;
use App\Models\User;
use App\Services\ComponentImportService;
use App\Services\ComponentSpreadsheetReader;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Localizable;
use Throwable;

/**
 * Imports an uploaded component spreadsheet, deletes the upload, and notifies the user who started it.
 */
class ImportComponentsJob implements ShouldQueue
{
    use Localizable, Queueable;

    public const string DISK = 'local';

    /**
     * A 5,000-row import takes a few seconds. Kept below the database queue's retry_after (90 s),
     * so the queue never starts this job again while it is still running.
     */
    public int $timeout = 60;

    /**
     * The upload is deleted after the first attempt, so there is nothing to retry.
     */
    public int $tries = 1;

    /**
     * @param  string  $path  The upload's path on the local disk.
     * @param  string  $locale  The language of the notification: the user's language when they started the import.
     */
    public function __construct(
        public string $path,
        public User $user,
        public string $locale,
    ) {}

    public function handle(ComponentSpreadsheetReader $reader, ComponentImportService $importService): void
    {
        try {
            $result = $this->import($reader, $importService);
        } finally {
            Storage::disk(self::DISK)->delete($this->path);
        }

        $this->withLocale($this->locale, fn () => $this->resultNotification($result)->sendToDatabase($this->user));
    }

    /**
     * Called when the job throws or times out. Nothing was saved, because the import runs in one transaction.
     */
    public function failed(?Throwable $exception): void
    {
        Storage::disk(self::DISK)->delete($this->path);

        $this->withLocale($this->locale, fn () => Notification::make()
            ->danger()
            ->title(__('components.import.notifications.failed_title'))
            ->body(__('components.import.notifications.crashed_body'))
            ->sendToDatabase($this->user));
    }

    private function import(ComponentSpreadsheetReader $reader, ComponentImportService $importService): ComponentImportResultData
    {
        $spreadsheet = $reader->read(Storage::disk(self::DISK)->path($this->path));

        if ($spreadsheet->hasErrors()) {
            $errors = new ImportErrorListData(
                array_slice($spreadsheet->errors, 0, ImportErrorListData::MAX_KEPT_ERRORS),
                count($spreadsheet->errors),
            );

            return new ComponentImportResultData(0, 0, 0, $errors);
        }

        return $importService->import($spreadsheet->rows);
    }

    private function resultNotification(ComponentImportResultData $result): Notification
    {
        if ($result->hasErrors()) {
            return Notification::make()
                ->danger()
                ->title(__('components.import.notifications.failed_title'))
                ->body($this->errorsBody($result->errors));
        }

        return Notification::make()
            ->success()
            ->title(__('components.import.notifications.succeeded_title'))
            ->body(__('components.import.notifications.succeeded_body', [
                'created' => $result->createdCount,
                'updated' => $result->updatedCount,
                'unchanged' => $result->unchangedCount,
            ]));
    }

    private function errorsBody(ImportErrorListData $errors): HtmlString
    {
        $lines = [e(__('components.import.notifications.failed_body', ['count' => $errors->totalCount]))];

        foreach ($errors->errors as $error) {
            $lines[] = e($this->errorLine($error));
        }

        $hiddenErrorCount = $errors->totalCount - count($errors->errors);

        if ($hiddenErrorCount > 0) {
            $lines[] = e(__('components.import.notifications.more_errors', ['count' => $hiddenErrorCount]));
        }

        return new HtmlString(implode('<br>', $lines));
    }

    private function errorLine(ImportRowErrorData $error): string
    {
        if ($error->rowNumber === null) {
            return $error->message;
        }

        return __('components.import.notifications.row_error', ['row' => $error->rowNumber, 'message' => $error->message]);
    }
}
