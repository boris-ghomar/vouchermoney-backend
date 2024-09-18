<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property string $name
 * @property float $balance
 * @property string $avatar
 * @property int $max_vouchers_count
 * @property float $max_voucher_amount
 * @property User $user
 */
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'balance',
        'avatar',
        'max_vouchers_count',
        'max_voucher_amount'
    ];

    protected $with = ["user"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->user->children();
    }

    public function isChild(User $user): bool
    {
        return $this->user->id === $user->id || $this->user->id === $user->parent_id;
    }
}
