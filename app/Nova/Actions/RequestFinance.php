<?php

namespace App\Nova\Actions;

use App\Models\Customer;
use App\Models\Finance\AbstractFinance;
use App\Models\Permission;
use App\Models\User;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Finance\Contracts\FinanceServiceContract;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
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

        return $user && $user->canAny([Permission::FINANCES_MANAGEMENT, Permission::CUSTOMER_FINANCE]);
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
     * @return ActionResponse
     */
    public function handle(ActionFields $fields): ActionResponse
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var FinanceServiceContract $financeService */
        $financeService = app(FinanceServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        $customer_id = $fields->get("");
        $comment = $fields->get("comment");
        $amount = $fields->get("amount");

        $customer = ! empty($customer_id) ? Customer::find($customer_id) : $user->customer;

        try {
            $financeService->{"request" . ucfirst($this->type)}($customer, $amount, $comment);
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["fields" => $fields]);
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

        $fields = [];

        if ($user && $user->is_admin) {
            /** @var CustomerServiceContract $customerService */
            $customerService = app(CustomerServiceContract::class);

            $select = Select::make(__("fields.customer"), 'customer_id')
                ->rules('required')->options($customerService->allCustomersPlucked())->searchable();

            if ($request->viaResource === "customers")
                $select->default(fn() => $request->viaResourceId)->readonly();

            $fields[] = $select;
        }

        $fields[] = Currency::make(__("fields.amount"), "amount")->rules('required', "min:1", "max:10000");

        $fields[] = Text::make(__("fields.comment"), 'comment')->rules('nullable', 'string');

        return $fields;
    }

    public static function make(...$arguments): array
    {
        $deposit = parent::make()->deposit()->confirmButtonText(__("actions.request"))->cancelButtonText(__("actions.cancel"));
        $withdraw = parent::make()->withdraw()->confirmButtonText(__("actions.request"))->cancelButtonText(__("actions.cancel"));
        return [$deposit, $withdraw];
    }
}
