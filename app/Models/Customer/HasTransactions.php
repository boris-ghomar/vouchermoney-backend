<?php

namespace App\Models\Customer;

use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read  Collection<Transaction>          $transactions
 * @property-read  Collection<ArchivedTransaction>  $archived_transactions
 */
trait HasTransactions
{
    use HasBalance;

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function archived_transactions(): HasMany
    {
        return $this->hasMany(ArchivedTransaction::class);
    }

    /**
     * Create deposit transaction
     *
     * @param   float       $amount Amount of transaction
     * @param   string      $description Description of transaction
     * @param   Model|null  $model Finance or Voucher - Model that caused the transaction to be created
     * @return  Transaction - created transaction
     * @throws  TransactionWithZeroAmount when amount is 0 or 0.0 - empty amount
     */
    public function deposit(float $amount, string $description, Model $model = null): Transaction
    {
        $amount = abs($amount);

        $this->checkValidityOfAmount($amount);

        return $this->transact($amount, $description, $model);
    }

    /**
     * Create withdrawal transaction
     *
     * @param   float       $amount Amount of transaction
     * @param   string      $description Description of transaction
     * @param   Model|null  $model Finance or Voucher - Model that caused the transaction to be created
     * @return  Transaction - created transaction
     * @throws  InsufficientBalance when customer have no available balance
     * @throws  TransactionWithZeroAmount when amount is 0 or 0.0 - empty amount
     */
    public function withdraw(float $amount, string $description, Model $model = null): Transaction
    {
        $amount = abs($amount);

        $this->canMakeWithdrawalTransaction($amount);

        return $this->transact($amount * -1, $description, $model);
    }

    /**
     * Create Transaction
     *
     * @param   float       $amount Amount of transaction
     * @param   string      $description Description of transaction
     * @param   Model|null  $model Finance or Voucher - Model that caused the transaction to be created
     * @return  Transaction - created transaction
     */
    private function transact(float $amount, string $description, Model $model = null): Transaction
    {
        $transaction = new Transaction();
        $transaction->customer_id = $this->id;
        $transaction->amount = $amount;
        $transaction->description = $description;

        if (! empty($model)) $transaction->model()->associate($model);

        $transaction->save();

        return $transaction;
    }

    /**
     * Check if amount is not empty and customer have available amount of balance for withdraw.
     *
     * @param   float  $amount  Amount that need to be checked
     * @return  void
     * @throws  InsufficientBalance when customer have no available balance
     * @throws  TransactionWithZeroAmount when amount is 0 or 0.0 - empty amount
     */
    private function canMakeWithdrawalTransaction(float $amount): void
    {
        $this->checkValidityOfAmount($amount);

        if (! $this->hasEnoughBalance($amount)) throw new InsufficientBalance();
    }

    /**
     * Check if amount is not empty value, such as 0 or 0.0
     *
     * @param   float  $amount Amount that need to be checked
     * @return  void
     * @throws  TransactionWithZeroAmount when amount is 0 or 0.0 - empty amount
     */
    private function checkValidityOfAmount(float $amount): void
    {
        if ( empty($amount) ) throw new TransactionWithZeroAmount();
    }
}
