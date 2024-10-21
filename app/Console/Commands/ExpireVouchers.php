<?php

namespace App\Console\Commands;

use App\Models\Voucher\Voucher;
use App\Services\Customer\CustomerService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireVouchers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vouchers:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire vouchers that have reached their expiration date';

    public function __construct(protected CustomerService $customerService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $expired_vouchers = Voucher::where('created_at', '<=', $threeMonthsAgo)->get();
        if ($expired_vouchers->isEmpty()) {
            $this->info('No vouchers to expire');
        }
        foreach ($expired_vouchers as $voucher) {
            try {
                $archived = $this->customerService->expireVoucher($voucher);
                $this->info("Expired voucher: {$archived->code}");
            } catch (\Exception $e) {
                $this->error("Failed to expire vocuher {$voucher->code}" . $e->getMessage());
            }
        }
    }
}
