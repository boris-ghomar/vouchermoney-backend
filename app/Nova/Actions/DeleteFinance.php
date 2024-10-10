<?php

namespace App\Nova\Actions;

use App\Models\Finance\Finance;
use App\Models\Permission;
use App\Models\User;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
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

    /**
     * Perform the action on the given models.
     *
     * @param  Collection<Finance>  $models
     * @return ActionResponse
     */
    public function handle(Collection $models): ActionResponse
    {
        /** @var CustomerServiceContract $customerService */
        $customerService = app(CustomerServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        try {
            $customerService->cancelRequests($models);
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["finances" => $models]);
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::message("Financial requests canceled");
    }

    public static function make(...$arguments): static
    {
        return parent::make(...$arguments)
            ->confirmButtonText(__("actions.delete"))
            ->cancelButtonText(__("actions.cancel"))
            ->confirmText(__("actions.sure_delete"));
    }
}
