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
        "amount" => "decimal:2",
        "archived_at" => "datetime",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    protected $fillable = [
        'customer_id',
        'amount',
        'description',
        'model_type',
        'model_id',
        'archived_at',
    ];

    public static function make(Transaction $transaction): static
    {
        $archivedTransaction = new static();
        $archivedTransaction->id = $transaction->id;
        $archivedTransaction->customer_id = $transaction->customer_id;
        $archivedTransaction->amount = $transaction->amount;
        $archivedTransaction->description = $transaction->description;

        if (! empty($transaction->model_id)) $archivedTransaction->model_id = $transaction->model_id;
        if (! empty($transaction->model_type)) $archivedTransaction->model_type = $transaction->model_type;

        $archivedTransaction->created_at = $transaction->created_at;
        $archivedTransaction->updated_at = $transaction->updated_at;
        $archivedTransaction->save();

        return $archivedTransaction;
    }
}
