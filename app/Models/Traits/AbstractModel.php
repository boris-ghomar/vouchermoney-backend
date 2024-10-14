<?php

namespace App\Models\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @method  int  count()
 *
 * @method  static  int  count()
 */
trait AbstractModel
{
    use LogsActivity;

    protected array $additional_fillable = [];
    protected array $additional_casts = [];
    protected array $additional_log_columns = [];

    public function getFillable(): array
    {
        return array_merge($this->fillable, $this->additional_fillable);
    }

    public function getCasts(): array
    {
        return array_merge($this->casts, $this->additional_casts);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(array_merge($this->logColumns(), $this->additional_log_columns));
    }

    abstract public function logColumns(): array;
}
