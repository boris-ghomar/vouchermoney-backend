<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use App\Models\Voucher;
use App\Services\JobActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Laravel\Nova\Notifications\NovaNotification;
use Exception;

class ProcessVoucherCreation implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected User $causer;
    protected Customer $customer;
    protected float $amount;
    protected Voucher $voucher

    /**
     * Create a new job instance.
     */
    public function __construct(User $causer, Customer $customer, float $amount, Voucher $voucher)
    {
        $this->customer = $customer;
        $this->amount = $amount;
        $this->causer = $causer;
        $this->voucher = $voucher;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

    }

    protected function log(string $message, string $status = "failed"): void
    {
        $log = JobActivityLogger::make(static::class)
            ->performedOn($this->customer)
            ->causedBy($this->causer)
            ->withMessage($message);

        if ($status === "failed") $log->withFailedStatus();
        else $log->withSuccessStatus();

        $log->log();

        $this->customer->notify($message, $status === "failed" ? "error" : "info");

    }


}
