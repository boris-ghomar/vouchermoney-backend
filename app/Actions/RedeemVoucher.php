<?php

namespace App\Actions;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Exceptions\NotAuthorized;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Models\Voucher\VoucherActivity;
use Illuminate\Support\Facades\DB;

class RedeemVoucher
{
    public function __construct(
        protected User          $user,
        protected Voucher       $voucher,
        protected string        $note = "",
        protected Customer|null $recipient = null
    ) {}

    /**
     * @throws NotAuthorized
     * @throws AttemptToRedeemFrozenVoucher
     */
    public function execute(): void
    {
        $this->authorizedToRun();

        $this->validate();

        DB::transaction(function () {
            // Get the recipient (customer) of voucher's amount
            $recipient = !empty($this->recipient) ? $this->recipient : $this->voucher->customer;

            // Create deposit transaction for recipient customer by the voucher's amount
            $recipient->deposit($this->voucher->amount, "Redeem voucher [" . $this->voucher->code . "]");

            // Make voucher archived (resolved) by creating instance in database
            $archivedVoucher = ArchivedVoucher::make(
                $this->voucher,
                ArchivedVoucher::STATE_REDEEMED,
                $this->note,
                $recipient
            );

            // Make activity log for voucher, that he changes his state
            VoucherActivity::make(
                $this->voucher->code,
                VoucherActivity::STATE_REDEEMED,
                $this->user,
                $this->getVoucherRedeemedActivityDescription()
            );

            // Delete active voucher from database (Archived version of voucher still stored in database)
            $this->voucher->delete();

            return $archivedVoucher;
        });

        $this->voucher->redeem();
    }

    /**
     * @throws AttemptToRedeemFrozenVoucher
     */
    protected function validate(): void
    {
        if (! $this->voucher->canBeRedeemed())
            throw new AttemptToRedeemFrozenVoucher();
    }

    /**
     * @throws NotAuthorized
     */
    protected function authorizedToRun(): void
    {
        if ($this->user->is_customer_admin || $this->user->can(Permission::CUSTOMER_VOUCHER_REDEEM))
            return;

        throw new NotAuthorized();
    }
}
