<?php

namespace App\Models\Voucher;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property  string  $code
 */
class VoucherCode extends Model
{
    use LogsActivity;

    protected $table = "voucher_codes";
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = "string";
    protected $primaryKey = "code";
    protected $fillable = ["code"];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code']);
    }
}
