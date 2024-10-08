<?php

namespace App\Models;

use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\Transaction\Transaction;
use App\Models\Voucher\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;
use Laravel\Nova\Notifications\NovaNotification;
use Closure;

/**
 * @property  string       $id
 * @property  string       $name
 * @property  float        $balance
 * @property  string|null  $avatar
 * @property  string       $type
 * @property  Carbon|null  $created_at
 * @property  Carbon|null  $updated_at
 *
 * @property  Collection<User>                 $users
 * @property  Collection<Transaction>          $transactions
 * @property  Collection<ArchivedTransaction>  $archived_transactions
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $keyType = 'string';
    public $incrementing = false;

    const TYPE_RESELLER = "reseller";
    const TYPE_MERCHANT = "merchant";
    const LIMIT_TOKEN = 5;
    protected $fillable = [
        'name',
        'balance',
        'avatar',
        'type'
    ];

    protected static function boot(): void
    {
        parent::boot();

        parent::deleted(function (Customer $model) {
            $model->users()->delete();
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, "customer_id");
    }

    public function calculateBalance(): float
    {
        $balance = $this->balance;

        foreach ($this->transactions()->get() as $transaction) {
            $balance += $transaction->amount;
        }

        return $balance;
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }

    public function archivedFinances(): HasMany
    {
        return $this->hasMany(ArchivedFinance::class);
    }

    /**
     * @param string $message
     * @param string $visitActionUrl
     * @param string $type Supported types - info, success, error or warning
     * @param string $icon
     * @return void
     */
    public function notify(string $message, string $visitActionUrl = "", string $type = "info", string $icon = "", bool $onlyAdmin = false): void
    {
        if ($onlyAdmin) {
            $users = $this->getAdminUser();
        } else {
            $users = $this->users->filter(function ($user) {
                return $user->can("customer:notifications");
            });
        }

        $notification = (new NovaNotification())
            ->message($message)
            ->type($type);

        if ($visitActionUrl) $notification->action(__("actions.view"), $visitActionUrl);

        if ($icon) $notification->icon($icon);

        Notification::send($users, $notification);
    }

    public function notifyAdministrator(string $message, string $visitActionUrl = "", string $type = "info", string $icon = ""): void
    {
        $this->notify($message, $visitActionUrl, $type, $icon, true);
    }

    public function sendFinanceRequestedNotification(Finance $finance): void
    {
        $message = __("notifications.customer_finance_requested" . ($finance->user_id === $this->getAdminUserId() ? "" : "_by"), [
            "amount" => $finance->amount,
            "type" => $finance->type,
            "by" => $finance->user->name
        ]);

        $this->notifyAdministrator($message, "/resources/finances/" . $finance->id);
    }

    /**
     * @param string $financeId
     * @param bool $state true if approved, false if rejected
     * @return void
     */
    public function sendFinanceResolvedNotification(string $financeId, bool $state): void
    {
        $this->notify(
            __("notifications.customer_finance", ["status" => __("actions." . ($state ? "approved" : "rejected"))]),
            "/resources/archived-finances/" . $financeId,
            $state ? "success" : "error", "currency-dollar");
    }

    public function hasEnoughBalance(float $amount): bool
    {
        $amount = abs($amount);

        return $this->calculateBalance() >= $amount;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @throws TransactionWithZeroAmount
     */
    public function deposit(float $amount, string $description = ""): Transaction
    {
        $amount = abs($amount);

        $this->checkValidityOfAmount($amount);

        return $this->transact($amount, $description);
    }

    /**
     * @throws InsufficientBalance|TransactionWithZeroAmount
     */
    public function withdraw(float $amount, string $description = ""): Transaction
    {
        $amount = abs($amount);

        $this->checkAbilityToMakeWithdrawalTransaction($amount);

        return $this->transact($amount * -1, $description);
    }

    private function transact(float $amount, string $description = ""): Transaction
    {
        $transaction = new Transaction();
        $transaction->customer_id = $this->id;
        $transaction->amount = $amount;

        if (!empty($description)) $transaction->description = $description;

        $transaction->save();

        return $transaction;
    }

    public function generateVoucher(float $amount): Voucher
    {
        return Voucher::generate($this, $amount);
    }
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * @throws TransactionWithZeroAmount
     */
    private function checkValidityOfAmount(float $amount): void
    {
        if (empty($amount)) throw new TransactionWithZeroAmount();
    }

    /**
     * @throws TransactionWithZeroAmount|InsufficientBalance
     */
    private function checkAbilityToMakeWithdrawalTransaction(float $amount): void
    {
        $this->checkValidityOfAmount($amount);

        if (!$this->hasEnoughBalance($amount)) throw new InsufficientBalance();
    }

    public function requestWithdraw(User $requestedBy, float $amount, string $comment): Finance
    {
        return Finance::withdraw($requestedBy, $this, $amount, $comment);
    }

    public function requestDeposit(User $requestedBy, float $amount, string $comment): Finance
    {
        return Finance::deposit($requestedBy, $this, $amount, $comment);
    }

    public static function toOptionsArray(): Closure
    {
        return fn() => Customer::query()->select(["id", "name"])
            ->pluck('name','id')->toArray();
    }

    public function getAdminUser(): User
    {
        return $this->users()->oldest()->first();
    }

    public function getAdminUserId(): string
    {
        return $this->users()->oldest()->select("id")->limit(1)->first()->id;
    }
    public function apiTokens()
    {
        return $this->hasMany(CustomerApiToken::class);
    }

}
