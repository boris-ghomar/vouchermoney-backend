<?php

namespace App\Models;

use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;
use Laravel\Nova\Notifications\NovaNotification;

/**
 * @property  int          $id
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
    use HasFactory, SoftDeletes;

    const TYPE_RESELLER = "reseller";
    const TYPE_MERCHANT = "merchant";

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

        foreach ($this->transactions as $transaction) {
            $balance += $transaction->amount;
        }

        return $balance;
    }

    /**
     * @param string $message
     * @param string $type Supported types - info, success, error or warning
     * @return void
     */
    public function notify(string $message, string $type = "info"): void
    {
        $users = $this->users->filter(function ($user) {
            return $user->can("customer:notifications");
        });

        Notification::send(
            $users,
            (new NovaNotification())
                ->message($message)
                ->type($type)
        );
    }

    public function hasEnoughBalance(float $amount): bool
    {
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

        return $this->transact(abs($amount) * -1, $description);
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
}
