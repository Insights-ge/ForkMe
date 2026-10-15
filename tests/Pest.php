<?php

use App\Enums\ComponentImportColumn;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Writes the rows to a temporary .xlsx file and returns its path. Each row is a list of cell values; an empty list is a blank row.
 *
 * @param  list<list<mixed>>  $rows
 */
function writeSpreadsheet(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'import-test-') . '.xlsx';
    $writer = new Writer;
    $writer->openToFile($path);

    foreach ($rows as $row) {
        $writer->addRow(Row::fromValues($row));
    }

    $writer->close();

    return $path;
}

/**
 * @return list<string>
 */
function componentImportHeaders(): array
{
    return array_column(ComponentImportColumn::cases(), 'value');
}

/**
 * A valid component spreadsheet row, keyed by header.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function componentImportRow(array $overrides = []): array
{
    return array_merge([
        'type' => 'პროფილი გვერდითა',
        'unique_code' => '000015193',
        'code' => 'გალუ4108',
        'description' => 'პროფილი. ნობელალუმინა XGJB-1058P ვწ. ორმხრივი გვერდი',
        'color' => 'შავი',
    ], $overrides);
}
