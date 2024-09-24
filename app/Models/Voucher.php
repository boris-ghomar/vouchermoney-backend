<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property  int          $id
 * @property  string       $code
 * @property  float        $amount
 * @property  string       $status
 * @property  int|null     $used_by
 * @property  int          $created_by
 * @property  Carbon|null  $resolved_at
 * @property  Carbon|null  $deleted_at
 * @property  Carbon|null  $created_at
 * @property  Carbon|null  $updated_at
 *
 */
class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = "active";
    const STATUS_BLOCKED = "blocked";
    const STATUS_CANCELED = "canceled";
    const STATUS_TRANSFERRED = "transferred";
    const STATUS_EXPIRED = "expired";

    protected $fillable = [
        'code',
        'amount',
        'status',
        'used_by',
        'created_by',
        'resolved_at'
    ];

    protected $casts = [
        "resolved_at" => "datetime"
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
