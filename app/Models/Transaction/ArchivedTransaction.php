<?php

namespace App\Models\Transaction;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property  Carbon       $archived_at
 */
class ArchivedTransaction extends AbstractTransaction
{
    protected $table = "archived_transactions";

    public $timestamps = false;

    protected $casts = [
        "archived_at" => "datetime",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    protected $fillable = [
        'customer_id',
        'amount',
        'archived_at'
    ];

    public static function make(Transaction $transaction): static
    {
        $archivedTransaction = new static();
        $archivedTransaction->id = $transaction->id;
        $archivedTransaction->customer_id = $transaction->customer->id;
        $archivedTransaction->amount = $transaction->amount;

        if (!empty($transaction->description)) $archivedTransaction->description = $transaction->description;

        $archivedTransaction->created_at = $transaction->created_at;
        $archivedTransaction->updated_at = $transaction->updated_at;
        $archivedTransaction->save();

        return $archivedTransaction;
    }
}
