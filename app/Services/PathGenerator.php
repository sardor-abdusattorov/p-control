<?php

namespace App\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PathGenerator implements \Spatie\MediaLibrary\Support\PathGenerator\PathGenerator
{
    public function getPath(Media $media): string
    {
        $modelName = class_basename($media->model_type);

        return 'files/' . strtolower($modelName) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
