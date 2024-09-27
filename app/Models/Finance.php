<?php

namespace App\Models;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Textarea;

/**
 * @property float $amount
 * @property string $request_comment
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $customer_id
 *
 * @property  User  $resolver
 */
class Finance extends Model
{
    use HasFactory;

    const STATUS_PENDING = "pending";
    const ACTION_APPROVE = "approve";
    const ACTION_REJECT = "reject";
    const ACTION_CANCEL = "cancel";

    protected $fillable = [
        'amount',
        'request_comment',
        'customer_id'
    ];

    /**
     * Relationship to the customer
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo( Customer::class);
    }


}
