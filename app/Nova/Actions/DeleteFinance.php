<?php

namespace App\Nova\Actions;

use App\Models\Finance\Finance;
use App\Models\Permission;
use App\Models\User;
use App\Nova\Resources\Finance\ActiveFinance;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Finance\Contracts\FinanceServiceContract;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Lednerb\ActionButtonSelector\ShowAsButton;

class DeleteFinance extends DestructiveAction
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $showInline = true;

    public function name(): string
    {
        return __("actions.delete");
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user && $user->can(Permission::CUSTOMER_FINANCE);
    }

    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var FinanceServiceContract $financeService */
        $financeService = app(FinanceServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        try {
            $financeService->cancelRequests($models);
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["finances" => $models]);
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::redirect("/resources/" . ActiveFinance::uriKey());
    }

    public static function make(...$arguments): static
    {
        return parent::make(...$arguments)
            ->confirmButtonText(__("actions.delete"))
            ->cancelButtonText(__("actions.cancel"))
            ->confirmText(__("actions.sure_delete"));
    }
}
