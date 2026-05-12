<?php

namespace App\Support;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

use function is_string;

class MediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->directory($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->directory($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->directory($media) . '/responsive/';
    }

    private function directory(Media $media): string
    {
        if (method_exists($media->model_type, 'mediaDirectory')) {
            $media->loadMissing('model');
            $directory = $media->getRelationValue('model')?->mediaDirectory();

            if (is_string($directory)) {
                return $directory;
            }
        }

        return Str::plural(Str::kebab(class_basename($media->model_type)));
    }
}
