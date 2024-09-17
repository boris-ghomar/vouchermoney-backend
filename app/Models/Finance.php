<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Illuminate\Database\Eloquent\Relations\MorphOne;

class Finance extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'type',
        'amount',
        'request_comment',
        'status',
        'approved_amount',
        'approved_comment',
        'created_by',
        'updated_by'
    ];

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class,'model');
    }
}
