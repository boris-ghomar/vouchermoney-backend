<?php

namespace App\Nova\Resources\Transaction;

use App\Models\Transaction\Transaction as Model;

class ActiveTransactions extends Transaction
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;
}
