<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['reason', 'description', 'status'])]
class Report extends Model
{
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}
