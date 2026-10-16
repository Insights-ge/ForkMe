<?php

namespace App\Actions;

use App\Models\Component;
use App\Models\User;
use App\Services\ComponentImportSampleWriter;
use Illuminate\Support\Facades\Gate;

class CreateComponentImportSampleAction
{
    public function __construct(private readonly ComponentImportSampleWriter $sampleWriter) {}

    /**
     * @param  string  $path  A file path, or "php://output" to stream it.
     */
    public function execute(User $user, string $path): void
    {
        Gate::forUser($user)->authorize('import', Component::class);

        $this->sampleWriter->write($path);
    }
}
