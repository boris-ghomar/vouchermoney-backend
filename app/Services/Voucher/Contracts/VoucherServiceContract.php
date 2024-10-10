<?php

namespace App\Services\Voucher\Contracts;

use App\Models\Customer;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;

interface VoucherServiceContract
{
    /**
     * Creates a new voucher instance for the specified customer with the given amount.
     * This method does not include additional business logic, such as validation or
     * eligibility checks. It simply creates a new Voucher object and returns it.
     *
     * @param Customer $customer The customer for whom the voucher is being generated.
     * @param float $amount The amount to be assigned to the new voucher.
     * @return Voucher The created voucher instance.
     */
    public function generate(Customer $customer, float $amount): Voucher;

    /**
     * Freezes the specified voucher, making it temporarily unusable.
     * This method updates the voucher's status to indicate that it is frozen,
     * preventing it from being redeemed until it is activated again.
     *
     * @param Voucher $voucher The voucher to be frozen.
     * @return Voucher The updated voucher instance with the frozen status.
     */
    public function freeze(Voucher $voucher): Voucher;

    /**
     * Activates the specified voucher, making it usable again.
     * This method updates the voucher's status to indicate that it is active,
     * allowing the customer to use it for transactions.
     *
     * @param Voucher $voucher The voucher to be activated.
     * @return Voucher The updated voucher instance with the active status.
     */
    public function activate(Voucher $voucher): Voucher;

    /**
     * Creates an instance of an archived voucher for the specified voucher redemption.
     * This method does not include additional business logic or validation.
     * It simply generates a new `ArchivedVoucher` instance to represent the redeemed state.
     *
     * If a recipient is provided, the redemption is associated with that recipient.
     * If no recipient is specified, the voucher creator (the customer who generated the voucher)
     * is assigned as the recipient, effectively redeeming or canceling their own voucher.
     *
     * @param Voucher $voucher The voucher that is being redeemed.
     * @param Customer|null $recipient The customer receiving the value of the voucher, or null if the creator should redeem their own voucher.
     * @param string $note Optional note for the redemption process (default is an empty string).
     * @return ArchivedVoucher The created archived voucher instance representing the redeemed voucher.
     */
    public function redeem(Voucher $voucher, Customer $recipient = null, string $note = ""): ArchivedVoucher;

    /**
     * Creates an instance of an archived voucher for the specified voucher expiration.
     * This method does not include additional business logic or validation.
     * It simply generates a new `ArchivedVoucher` instance to represent the expired state.
     *
     * @param Voucher $voucher The voucher that is being expired.
     * @return ArchivedVoucher The created archived voucher instance representing the expired voucher.
     */
    public function expire(Voucher $voucher): ArchivedVoucher;

    /**
     * Deletes the specified voucher.
     * This method removes the voucher from the database, making it no longer available
     * for any operations. It ensures that any related resources or records are properly
     * handled before the deletion.
     *
     * Note: This method performs a permanent deletion and should be used with caution,
     * as the voucher data will be irreversibly removed.
     *
     * @param Voucher $voucher The voucher to be deleted.
     * @return void
     */
    public function delete(Voucher $voucher): void;
}
