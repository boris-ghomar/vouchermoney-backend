<?php

namespace App\Models\Transaction;

use Carbon\Carbon;

/**
 * @property  Carbon  $archived_at
 */
class ArchivedTransaction extends AbstractTransaction
{
    protected $table = "archived_transactions";
    public $timestamps = false;
    protected array $additional_casts = ["archived_at" => "datetime"];
    protected array $additional_fillable = ["archived_at"];
    protected array $additional_log_columns = ["archived_at"];
}
