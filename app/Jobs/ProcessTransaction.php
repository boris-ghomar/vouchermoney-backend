<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessTransaction implements ShouldQueue
{
    use Queueable, Dispatchable;

    public Customer $issuer;
    public Customer|null $recipient;
    public float $amount;

    /**
     * Create a new job instance.
     */
    public function __construct(Customer $issuer, float $amount, Customer $recipient = null)
    {
        $this->issuer = $issuer;
        $this->amount = $amount;
        $this->recipient = $recipient;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->issuer->id)];
    }
}
