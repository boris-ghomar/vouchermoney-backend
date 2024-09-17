<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'model_id',
        'model_type',
        'status',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo("model");
    }
}
