<?php

use App\Actions\CreateComponentImportSampleAction;
use App\Actions\DispatchComponentImportAction;
use App\Actions\PreviewComponentImportAction;
use App\Enums\Role as RoleEnum;
use App\Jobs\ImportComponentsJob;
use App\Models\Component;
use App\Models\User;
use App\Services\ComponentSpreadsheetReader;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([UserSeeder::class, RoleSeeder::class]);

    $this->admin = User::where('email', 'admin@ltb.ge')->first();
    $this->user = User::factory()->create();
    $this->user->assignRole(RoleEnum::User->value);
});

test('the preview counts what the file would change', function () {
    $path = writeSpreadsheet([componentImportHeaders(), array_values(componentImportRow())]);

    $preview = app(PreviewComponentImportAction::class)->execute($this->admin, $path);

    expect($preview->hasErrors())->toBeFalse()
        ->and($preview->toCreateCount)->toBe(1)
        ->and(Component::query()->count())->toBe(0);
});

test('the preview reports invalid rows', function () {
    $path = writeSpreadsheet([componentImportHeaders(), array_values(componentImportRow(['type' => 'უცნობი']))]);

    $preview = app(PreviewComponentImportAction::class)->execute($this->admin, $path);

    expect($preview->hasErrors())->toBeTrue()
        ->and($preview->errors->errors[0]->rowNumber)->toBe(2)
        ->and($preview->errors->errors[0]->column)->toBe('type');
});

test('the preview reports errors about the whole file', function () {
    $path = writeSpreadsheet([['type'], ['ტიხარი']]);

    $preview = app(PreviewComponentImportAction::class)->execute($this->admin, $path);

    expect($preview->hasErrors())->toBeTrue()
        ->and($preview->errors->totalCount)->toBe(4)
        ->and($preview->errors->errors[0]->rowNumber)->toBeNull();
});

test('the import is queued for the user in their language', function () {
    Storage::fake(ImportComponentsJob::disk());
    Queue::fake();
    Storage::disk(ImportComponentsJob::disk())->put('component-imports/upload.xlsx', 'content');

    app(DispatchComponentImportAction::class)->execute($this->admin, 'component-imports/upload.xlsx', 'ka');

    Queue::assertPushed(ImportComponentsJob::class, fn (ImportComponentsJob $job): bool => $job->path === 'component-imports/upload.xlsx'
        && $job->user->is($this->admin)
        && $job->locale === 'ka');
});

test('an import is not queued when the upload does not exist', function () {
    Storage::fake(ImportComponentsJob::disk());
    Queue::fake();

    expect(fn () => app(DispatchComponentImportAction::class)->execute($this->admin, 'component-imports/missing.xlsx', 'ka'))
        ->toThrow(InvalidArgumentException::class);

    Queue::assertNothingPushed();
});

test('the sample has only the header row', function () {
    $path = tempnam(sys_get_temp_dir(), 'import-test-') . '.xlsx';

    app(CreateComponentImportSampleAction::class)->execute($this->admin, $path);

    $sample = app(ComponentSpreadsheetReader::class)->read($path);

    expect($sample->hasErrors())->toBeFalse()
        ->and($sample->rows)->toBeEmpty();
});

test('users without the import permission cannot preview, import or download the sample', function () {
    Storage::fake(ImportComponentsJob::disk());
    Queue::fake();
    Storage::disk(ImportComponentsJob::disk())->put('component-imports/upload.xlsx', 'content');
    $path = writeSpreadsheet([componentImportHeaders(), array_values(componentImportRow())]);

    expect(fn () => app(PreviewComponentImportAction::class)->execute($this->user, $path))->toThrow(AuthorizationException::class)
        ->and(fn () => app(DispatchComponentImportAction::class)->execute($this->user, 'component-imports/upload.xlsx', 'ka'))->toThrow(AuthorizationException::class)
        ->and(fn () => app(CreateComponentImportSampleAction::class)->execute($this->user, tempnam(sys_get_temp_dir(), 'import-test-')))->toThrow(AuthorizationException::class);

    Queue::assertNothingPushed();
});

test('super admins can use the import actions', function () {
    $superAdmin = User::where('email', 'demo@demo.com')->first();
    $path = writeSpreadsheet([componentImportHeaders(), array_values(componentImportRow())]);

    expect(app(PreviewComponentImportAction::class)->execute($superAdmin, $path)->toCreateCount)->toBe(1);
});
