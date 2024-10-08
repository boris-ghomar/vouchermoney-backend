<?php

namespace App\Models\Customer;

use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read  Collection<Voucher>          $vouchers
 * @property-read  Collection<ArchivedVoucher>  $archived_vouchers
 */
trait HasVouchers
{
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function archived_vouchers(): HasMany
    {
        return $this->hasMany(ArchivedVoucher::class, "customer_data->id");
    }

    public function generateVoucher(float $amount): Voucher
    {
        return Voucher::generate($this, $amount);
    }
}
