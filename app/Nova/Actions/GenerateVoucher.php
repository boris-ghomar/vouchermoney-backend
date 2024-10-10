<?php

namespace App\Nova\Actions;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use App\Nova\Fields\Currency;
use App\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class GenerateVoucher extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $onlyOnIndex = true;
    public $standalone = true;
    public $confirmText = "";

    public function name(): string
    {
        return __("actions.generate");
    }

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user && $user->canCustomer(Permission::CUSTOMER_VOUCHER_GENERATE);
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
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

        $count = +$fields->count ?: 1;
        $amount = (float) $fields->amount;

        if (!$user->customer->hasEnoughBalance($count * $amount)) {

            activity(static::class)
                ->causedBy($user)
                ->withProperties([
                    "user" => $user,
                    "fields" => $fields,
                    "count" => $count,
                    "amount" => $amount,
                    "balance" => $user->customer->available_balance
                ])->log("Attempt to generate voucher without enough balance");

            return ActionResponse::danger("Insufficient balance for that action");
        }

        try {
            $vouchers = [];
            for ($i = 0; $i < $count; $i++) $vouchers[] = $user->customer->generateVoucher($amount);
        } catch (Exception $exception) {
            activity(static::class)
                ->causedBy($user)
                ->performedOn($user->customer)
                ->withProperties([
                    "exception" => $exception->getMessage(),
                    "user" => $user,
                    "customer" => $user->customer,
                    "vouchers" => $vouchers,
                ])->log("Failed to generate vouchers");
            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Generated!");
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
            Number::make(__("fields.count"), "count")->default(1)->rules("required", "min:1", "max:50"),
            Currency::make(__("fields.amount"), "amount")->rules("required", "min:1", "max:1000")
        ];
    }

    public static function make(...$arguments): static
    {
        return parent::make(...$arguments)
            ->confirmButtonText(__("actions.generate"))
            ->cancelButtonText(__("actions.cancel"));
    }
}
