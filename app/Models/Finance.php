<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Nova\Fields\BelongsTo;

/**
 * @property float $amount
 * @property string $request_comment
 * @property  string $status
 * @property string $approved_comment
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $resolved_by
 * @property int $customer_id
 */
class Finance extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'request_comment',
        'status',
        'approved_comment',
        'resolved_by',
        'customer_id'
    ];

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class,'model');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
