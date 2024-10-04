<?php

namespace App\Models\Customer;

use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read  Collection<Finance>          $finances
 * @property-read  Collection<ArchivedFinance>  $archived_finances
 */
trait HasFinances
{
    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }

    public function archivedFinances(): HasMany
    {
        return $this->hasMany(ArchivedFinance::class);
    }

    public function requestWithdraw(User $requester, float $amount, string $comment): Finance
    {
        return Finance::withdraw($requester, $this, $amount, $comment);
    }

    public function requestDeposit(User $requester, float $amount, string $comment): Finance
    {
        return Finance::deposit($requester, $this, $amount, $comment);
    }
}
