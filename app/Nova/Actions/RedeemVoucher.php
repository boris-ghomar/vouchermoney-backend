<?php

namespace App\Nova\Actions;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\Voucher;
use App\Nova\Fields\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
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
    public $onlyOnIndex = true;

    public function name(): string
    {
        return __("actions.redeem");
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user && $user->canCustomer(Permission::CUSTOMER_VOUCHER_REDEEM);
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

        $code = $fields->code;
        $note = $fields->note ?: "";

        $voucher = Voucher::findByCode($code);

        if (! $voucher) {
            activity(static::class)
                ->causedBy($user)
                ->withProperties([
                    "user" => $user,
                    "fields" => $fields
                ])->log("Attempt to redeem voucher that not exists");
            return ActionResponse::danger("Voucher not found");
        }

        if (! $voucher->active) {
            activity(static::class)
                ->causedBy($user)
                ->withProperties([
                    "user" => $user,
                    "fields" => $fields,
                    "voucher" => $voucher
                ])->log("Attempt to redeem voucher that frozen");

            return ActionResponse::danger("Voucher frozen");
        }

        try {
            $voucher->redeem($note, $voucher->customer_id === $user->customer_id ? null : $user->customer_id);
        } catch (Exception $exception) {
            activity(static::class)
                ->withProperties([
                    "exception" => $exception->getMessage(),
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
                ->rules("required"),

            Text::make(__("fields.note"), "note")
                ->rules("nullable", "string", "max:200")
        ];
    }

    public static function make(...$arguments): static
    {
        return parent::make()
            ->confirmButtonText(__("actions.redeem"))
            ->cancelButtonText(__("actions.cancel"));
    }
}
