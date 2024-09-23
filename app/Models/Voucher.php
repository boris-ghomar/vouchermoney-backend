<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property  int     $id
 * @property  string  $code
 * @property  float   $amount
 * @property  string  $status
 * @property  int     $used_by
 * @property  int     $created_by
 * @property  Carbon  $deleted_at
 * @property  Carbon  $created_at
 * @property  Carbon  $updated_at
 *
// * @property User $issuer
// * @property User $recipient
 */
class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'amount',
        'status',
        'used_by',
        'created_by',
    ];

//    public function issuer(): BelongsTo
//    {
//        return $this->belongsTo(User::class, 'created_by');
//    }
//
//    public function recipient(): BelongsTo
//    {
//        return $this->belongsTo(User::class, 'used_by');
//    }

//    public function transaction(): MorphOne
//    {
//        return $this->morphOne(Transaction::class, 'model');
//    }

}
