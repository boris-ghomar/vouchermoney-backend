<?php

namespace App\Models\Transaction;

use Carbon\Carbon;

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

        if (! empty($transaction->model)) $archivedTransaction->model()->associate($transaction->model);

        $archivedTransaction->created_at = $transaction->created_at;
        $archivedTransaction->updated_at = $transaction->updated_at;
        $archivedTransaction->save();

        return $archivedTransaction;
    }
}
