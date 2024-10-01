<?php

namespace App\Nova\Actions;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Models\User;
use App\Models\Voucher\Voucher;
use App\Models\Voucher\VoucherCode;
use App\Nova\Fields\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class RedeemVoucher extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $standalone = true;
    public $confirmText = "";

    public function name(): string
    {
        return __("actions.redeem");
    }

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $voucher_code = $fields->voucher_code;
        /** @var User $user */
        $user = auth()->user();

        if (!$user || !$user->customer?->id || !$user->can("customer:voucher:redeem"))
            return ActionResponse::danger("Not authorized to perform this action");

        /** @var Voucher $voucher */
        $voucher = Voucher::find($voucher_code);

        if (!$voucher) return ActionResponse::danger("Voucher not found");

        if (!$voucher->active) return ActionResponse::danger("Voucher frozen");

        try {
            $voucher->redeem($voucher->customer_id === $user->customer_id ? null : $user->customer_id);
        } catch (AttemptToRedeemFrozenVoucher $e) {
            return ActionResponse::danger($e->getMessage());
        } catch (Exception $exception) {
            activity(static::class)
                ->withProperties([
                    "exception" => $exception->getMessage(),
                    "voucher_code" => $voucher_code,
                    "voucher" => $voucher,
                    "user" => $user
                ])->causedBy($user)->log("Failed to redeem voucher");

            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Voucher successfully redeemed");
    }

    /**
     * Get the fields available on the action.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make("Voucher code", "voucher_code")
                ->rules("required", "size:" . VoucherCode::getVoucherCodeLength())
        ];
    }

    public static function make(...$arguments): static
    {
        return parent::make()
            ->confirmButtonText(__("actions.redeem"))
            ->cancelButtonText(__("actions.cancel"));
    }
}
