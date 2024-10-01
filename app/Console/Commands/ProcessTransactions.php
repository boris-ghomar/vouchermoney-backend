<?php

namespace App\Console\Commands;

use App\Exceptions\AttemptToArchiveTransactionWithoutCustomer;
use App\Models\Transaction\Transaction;
use Illuminate\Console\Command;
use Exception;

class ProcessTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws AttemptToArchiveTransactionWithoutCustomer
     */
    public function handle(): void
    {
        try {
            Transaction::query()->chunk(100, function ($transactions) {
                /** @var Transaction $transaction */
                foreach ($transactions as $transaction) $transaction->archive();
            });
        } catch (Exception $exception) {
            activity(static::class)
                ->withProperties([
                    "status" => "failed",
                    "exception" => $exception->getMessage(),
                ])->log("Failed to process transactions [schedule]");

            throw $exception;
        }
    }
}
