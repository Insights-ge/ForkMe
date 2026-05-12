<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
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
            $model = $media->getRelationValue('model');

            if (! $model instanceof Model || ! method_exists($model, 'mediaDirectory')) {
                return $this->fallbackDirectory($media);
            }

            $directory = $model->mediaDirectory();

            if (is_string($directory)) {
                return $directory;
            }
        }

        return $this->fallbackDirectory($media);
    }

    private function fallbackDirectory(Media $media): string
    {
        return Str::plural(Str::kebab(class_basename($media->model_type)));
    }
}
