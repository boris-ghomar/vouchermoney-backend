<?php

namespace App\Nova\Actions;

use App\Models\Permission;
use App\Models\User;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
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

        return $user && $user->can(Permission::CUSTOMER_VOUCHER_GENERATE);
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $count = (int) $fields->get("count");
        $amount = (float) $fields->get("amount");

        /** @var CustomerServiceContract $customerService */
        $customerService = app(CustomerServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        try {
            $customerService->generateVoucher($user->customer, $amount, $count);
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["fields" => $fields]);
            return ActionResponse::danger($exception->getMessage());
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
            Number::make(__("fields.count"), "count")->default(1)->rules("required",'numeric', "min:1", "max:50"),
            Currency::make(__("fields.amount"), "amount")->rules("required",'numeric', "min:1", "max:1000")
        ];
    }

    public static function make(...$arguments): static
    {
        return parent::make(...$arguments)
            ->confirmButtonText(__("actions.generate"))
            ->cancelButtonText(__("actions.cancel"));
    }
}
