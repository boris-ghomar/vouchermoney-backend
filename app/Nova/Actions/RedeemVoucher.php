<?php

namespace App\Nova\Actions;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\Voucher;
use App\Models\Voucher\VoucherCode;
use App\Nova\Fields\Text;
use App\Services\PermissionService;
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
        /** @var User $user */
        $user = auth()->user();

        if (!$user || !PermissionService::authorizedToRedeemVoucher($user))
            return ActionResponse::danger(__("exceptions.not_authorized_to_perform_action"));

        $code = $fields->code;
        $note = $fields->note ?: "";

        /** @var Voucher $voucher */
        $voucher = Voucher::find($code);

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
            Text::make(__("fields.code"), "code")
                ->rules("required", "size:" . VoucherCode::getVoucherCodeLength()),

            Text::make(__("fields.note"), "note")
                ->rules("nullable", "string")
        ];
    }

    public static function make(...$arguments): static
    {
        return parent::make()
            ->confirmButtonText(__("actions.redeem"))
            ->cancelButtonText(__("actions.cancel"));
    }
}
