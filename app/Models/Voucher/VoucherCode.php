<?php

namespace App\Models\Voucher;

use App\Exceptions\AttemptToStoreTwoIdenticalVoucherCode;
use Illuminate\Database\Eloquent\Model;
use Exception;

/**
 * @property  string  $code
 */
class VoucherCode extends Model
{
    protected $table = "voucher_codes";

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = "string";

    protected $primaryKey = "code";

    protected $fillable = [
        "code"
    ];

    const VOUCHER_GROUPS = 6;

    public static function generate(): string
    {
        $voucherCode = "";

        do {
            try {
                $voucherCode = static::make();
            } catch (AttemptToStoreTwoIdenticalVoucherCode $e) {
                activity("VoucherCode::generate")
                    ->withProperties([
                        "status" => "failed",
                        "error" => $e->getMessage(),
                        "message" => "Attempt to store two identical voucher codes"
                    ])->log("Some unique case");
            }
        } while (empty($voucherCode));

        return $voucherCode;
    }

    /**
     * @throws AttemptToStoreTwoIdenticalVoucherCode
     */
    private static function make(): string
    {
        // Define characters to use (exclude 0, O, I, 1, and l for readability)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        // Generate a voucher code using the allowed characters
        $voucherCode = '';

        for ($i = 0; $i < static::VOUCHER_GROUPS; $i++) {
            $voucherCode .= substr(str_shuffle($characters), 0, 4);

            // Add hyphen between groups
            if ($i < static::VOUCHER_GROUPS - 1) $voucherCode .= '-';
        }

        // Ensure uniqueness by checking the database
        while (static::query()->where('code', $voucherCode)->exists()) {
            // Re-generate the code if a duplicate is found
            $voucherCode = static::make();
        }

        try {
            $voucher = new static();
            $voucher->code = $voucherCode;
            $voucher->save();
        } catch (Exception $e) {
            throw new AttemptToStoreTwoIdenticalVoucherCode();
        }

        return $voucherCode;
    }
}
