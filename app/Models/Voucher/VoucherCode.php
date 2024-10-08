<?php

namespace App\Models\Voucher;

use Illuminate\Database\Eloquent\Model;
use Exception;
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
    protected $fillable = [
        "code"
    ];

    /**
     * Exclude 0, O, I, 1, and l for readability
     */
    const VOUCHER_AVAILABLE_CHARS = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    const VOUCHER_GROUPS = 6;
    const VOUCHER_GROUP_LENGTH = 4;

    /**
     * @throws Exception
     */
    public static function generate(): string
    {
        // Generate a voucher code using the allowed characters
        $voucherCode = '';

        for ($i = 0; $i < static::VOUCHER_GROUPS; $i++) {
            $voucherCode .= substr(str_shuffle(static::VOUCHER_AVAILABLE_CHARS), 0, static::VOUCHER_GROUP_LENGTH);

            // Add hyphen between groups
            if ($i < static::VOUCHER_GROUPS - 1) $voucherCode .= '-';
        }

        // Ensure uniqueness by checking the database
        while (static::query()->where('code', $voucherCode)->exists()) {
            // Re-generate the code if a duplicate is found
            $voucherCode = static::generate();
        }

        $voucher = new static();
        $voucher->code = $voucherCode;
        $voucher->save();

        return $voucherCode;
    }

    public static function getVoucherCodeLength(): int
    {
        return (static::VOUCHER_GROUPS * static::VOUCHER_GROUP_LENGTH) + (static::VOUCHER_GROUPS - 1);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code']);
    }
}
