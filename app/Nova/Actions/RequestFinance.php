<?php

namespace App\Nova\Actions;

use App\Exceptions\InsufficientBalance;
use App\Models\Customer;
use App\Models\Finance\AbstractFinance;
use App\Models\Finance\Finance;
use App\Models\User;
use Illuminate\Bus\Queueable;
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
        return $this->setType(AbstractFinance::TYPE_WITHDRAW);
    }

    public function deposit(): static
    {
        return $this->setType(AbstractFinance::TYPE_DEPOSIT);
    }

    private function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Override the name method to dynamically set the button name.
     *
     * @return string
     */
    public function name(): string
    {
        return "Request " . $this->type;
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
        /** @var User $authUser */
        $authUser = auth()->user();

        if (!$authUser) return ActionResponse::danger("Not authorized for that action");

        if (!empty($fields->customer_id)) $customer = Customer::find($fields->customer_id);
        else $customer = $authUser->customer;

        try {
            if ($this->type === AbstractFinance::TYPE_DEPOSIT) $customer->requestDeposit($authUser, $fields->amount, $fields->comment ?: "");
            else $customer->requestWithdraw($authUser, $fields->amount, $fields->comment ?: "");
        } catch (InsufficientBalance $exception) {
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
        $user = $request->user();
        $fields = [
            Currency::make(__("fields.amount"), "amount")->rules('required', "min:1", "max:10000"),
            Text::make(__("fields.comment"), 'comment')->rules('nullable'),
        ];

        if ($user?->is_admin)
            array_unshift($fields, Select::make(__("fields.customer"), 'customer_id')
                ->rules('required')->options(Customer::toOptionsArray())
                ->searchable());

        return $fields;
    }

    public static function make(...$arguments): array
    {
        $deposit = parent::make()->deposit()->confirmButtonText(__("actions.request"))->cancelButtonText(__("actions.cancel"));
        $withdraw = parent::make()->withdraw()->confirmButtonText(__("actions.request"))->cancelButtonText(__("actions.cancel"));
        return [$deposit, $withdraw];
    }
}
