<?php

namespace App\Nova\Actions;

use App\Exceptions\InsufficientBalance;
use App\Models\Customer\Customer;
use App\Models\Finance\AbstractFinance;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;

class RequestFinance extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    protected string $type;
    public $standalone = true;
    public $onlyOnIndex = true;
    public $confirmText = "";

    public function withdraw(): static
    {
        $this->type = AbstractFinance::TYPE_WITHDRAW;

        return $this;
    }

    public function deposit(): static
    {
        $this->type = AbstractFinance::TYPE_DEPOSIT;

        return $this;
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user && ($user->canAdmin(Permission::FINANCES_MANAGEMENT) || $user->canCustomer(Permission::CUSTOMER_FINANCE));
    }

    /**
     * Override the name method to dynamically set the button name.
     *
     * @return string
     */
    public function name(): string
    {
        return ucfirst($this->type);
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

        $customer_id = $fields->customer_id ?? null;
        $comment = $fields->comment ?: "";
        $amount = $fields->amount;

        $customer = !empty($customer_id) ? Customer::find($customer_id) : $user->customer;

        try {
            $customer->{"request" . ucfirst($this->type)}($user, $amount, $comment);
        } catch (InsufficientBalance $exception) {
            activity(static::class)
                ->causedBy($user)
                ->withProperties([
                    "user" => $user,
                    "fields" => $fields,
                    "exception" => $exception->getMessage()
                ])
                ->log("Failed to make finance request");
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::message("Successfully requested");
    }

    /**
     * Get the fields available on the action.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        /** @var User $user */
        $user = $request->user();

        $fields = [
            Currency::make(__("fields.amount"), "amount")->rules('required', "min:1", "max:10000"),
            Text::make(__("fields.comment"), 'comment')->rules('nullable', 'string'),
        ];

        if ($user?->is_admin)
            array_unshift($fields, Select::make(__("fields.customer"), 'customer_id')
                ->rules('required')->options(Customer::pluck("name", "id"))->searchable());

        return $fields;
    }

    public static function make(...$arguments): array
    {
        $deposit = parent::make()->deposit()->confirmButtonText(__("actions.request"))->cancelButtonText(__("actions.cancel"));
        $withdraw = parent::make()->withdraw()->confirmButtonText(__("actions.request"))->cancelButtonText(__("actions.cancel"));
        return [$deposit, $withdraw];
    }
}
