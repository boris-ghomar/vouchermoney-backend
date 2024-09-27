<?php

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class GenerateVoucher extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $onlyOnIndex = true;

    public $standalone = true;

    public function name(): string
    {
        return __("actions.generate_voucher");
    }

    public function getConfirmButtonText(): string
    {
        return __("actions.generate");
    }

    public function getCancelButtonText(): string
    {
        return __("actions.cancel");
    }

    public $confirmText = "";

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

        if (empty($user) || empty($user->customer))
            return ActionResponse::danger(__("actions.customer_not_found"));

        $count = +$fields->count ?: 1;
        $amount = $fields->amount;
        $i = 0;

        if (!$user->customer->hasEnoughBalance($count * $amount)) {
            return ActionResponse::danger("Insufficient balance for that action");
        }

        try {
            for (; $i < $count; $i++) $user->customer->generateVoucher($amount);
        } catch (Exception $exception) {
            activity(static::class)
                ->causedBy($user)
                ->performedOn($user->customer)
                ->withProperties([
                    "user" => $user,
                    "customer" => $user->customer,
                    "error" => $exception->getMessage(),
                    "session" => session()->all()
                ])->log("Failed to generate voucher via Nova action");
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::message("Voucher" . ($count === 1 ? "" : "s") . " generated");
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Number::make(__("fields.count"), "count")->default(1)->rules("required", "min:1", "max:5"),
            Currency::make(__("fields.amount"), "amount")->rules("required", "min:1", "max:1000")
        ];
    }
}
