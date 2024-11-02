<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media as Model;

class Media extends Model
{
    use Cachable;

    public static function booted()
    {
        static::deleting(function (Media $deletedModel) {
            /**
             * Question:
             * 1. Is this method effective?
             */
            if ($deletedModel->submissionFiles()->exists()) {
                $deletedModel->submissionFiles()->each(function ($record) {
                    $record->reviewerAssginedFiles()->delete();
                    $record->delete();
                });
            }
        });
    }

    public function submissionFiles(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }
}
