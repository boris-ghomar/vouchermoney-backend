<?php

namespace App\Models\Traits;

use App\Models\Transaction\AbstractTransaction;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read  AbstractTransaction  $transaction
 * @property-read  Transaction          $activeTransaction
 * @property-read  ArchivedTransaction  $archivedTransaction
 */
trait HasTransaction
{
    public function activeTransaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, "transactionable");
    }

    public function archivedTransaction(): MorphOne
    {
        return $this->morphOne(ArchivedTransaction::class, "transactionable");
    }

    public function getTransactionAttribute(): AbstractTransaction|null
    {
        return $this->activeTransaction ?: $this->archivedTransaction;
    }
}
