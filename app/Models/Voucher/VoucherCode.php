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
    const VOUCHER_GROUP_LENGTH = 4;
    /**
     * Exclude 0, O, I, 1, and l for readability
     */
    const VOUCHER_AVAILABLE_CHARS = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";

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

    public static function getVoucherCodeLength(): int
    {
        return (static::VOUCHER_GROUPS * static::VOUCHER_GROUP_LENGTH) + (static::VOUCHER_GROUPS - 1);
    }

    /**
     * @throws AttemptToStoreTwoIdenticalVoucherCode
     */
    private static function make(): string
    {
        $characters = static::VOUCHER_AVAILABLE_CHARS;

        // Generate a voucher code using the allowed characters
        $voucherCode = '';

        for ($i = 0; $i < static::VOUCHER_GROUPS; $i++) {
            $voucherCode .= substr(str_shuffle($characters), 0, static::VOUCHER_GROUP_LENGTH);

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
