<?php

namespace App\Models;

use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\Transaction\Transaction;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Services\Customer\Contracts\CustomerServiceContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property  string       $id
 * @property  string       $name
 * @property  float        $balance
 * @property  string       $type
 * @property  Carbon|null  $deleted_at
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property-read  float  $available_balance
 *
 * @property-read  Collection<User>                 $users
 * @property-read  User                             $admin
 * @property-read  Collection<Transaction>          $transactions
 * @property-read  Collection<ArchivedTransaction>  $archived_transactions
 * @property-read  Collection<Finance>              $finances
 * @property-read  Collection<ArchivedFinance>      $archived_finances
 * @property-read  Collection<Voucher>              $vouchers
 * @property-read  Collection<ArchivedVoucher>      $archived_vouchers
 *
 * @method  static  static      find(string $id)
 * @method  static  Collection  pluck(string $value, string $key)
 */
class Customer extends Model
{
    use SoftDeletes, HasUlids, LogsActivity;

    const TYPE_RESELLER = "reseller";
    const TYPE_MERCHANT = "merchant";

    protected $fillable = ['name', 'balance', 'type'];

    protected $casts = ["balance" => "decimal:2"];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class)->oldestOfMany();
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(CustomerApiToken::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function archivedTransactions(): HasMany
    {
        return $this->hasMany(ArchivedTransaction::class);
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }

    public function archivedFinances(): HasMany
    {
        return $this->hasMany(ArchivedFinance::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function archivedVouchers(): HasMany
    {
        return $this->hasMany(ArchivedVoucher::class);
    }

    public function getAvailableBalanceAttribute(): float
    {
        /** @var CustomerServiceContract $service */
        $service = app(CustomerServiceContract::class);

        return $service->computeBalance($this);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'balance', "type", "created_at", "updated_at", "deleted_at", "available_balance"]);
    }
}
