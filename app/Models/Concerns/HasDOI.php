<?php

namespace App\Models\Concerns;

use App\Models\DOI;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasDOI
{
    /**
     * Get the model's DOI.
     */
    public function doi(): MorphOne
    {
        return $this->morphOne(DOI::class, 'doiable');
    }
}
