<?php

namespace App\Console\Commands;

use App\Models\Transaction\Transaction;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Transaction\Contracts\TransactionServiceContract;
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

    public function __construct(
        protected TransactionServiceContract $transactionService,
        protected ActivityServiceContract $activityService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            Transaction::query()->chunk(100, function ($transactions) {
                /** @var Transaction $transaction */
                foreach ($transactions as $transaction) $this->transactionService->archive($transaction);
            });
        } catch (Exception $exception) {
            $this->activityService->commandException($exception);
            throw $exception;
        }
    }
}
