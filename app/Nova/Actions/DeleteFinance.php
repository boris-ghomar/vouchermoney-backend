<?php

namespace App\Nova\Actions;

use App\Models\Finance\Finance;
use App\Models\Permission;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
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

        return $user && $user->canCustomer(Permission::CUSTOMER_FINANCE);
    }

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection<Finance>  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $user = auth()->user();

        try {
            foreach ($models as $model) $model->cancel();
        } catch (Exception $exception) {
            activity(static::class)
                ->causedBy($user)
                ->withProperties([
                    "user" => $user,
                    "finances" => $models,
                    "exception" => $exception->getMessage()
                ])
                ->log("Finance request deleting failed");
            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Financial requests cancelled");
    }

    /**
     * Get the fields available on the action.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }

    public static function make(...$arguments): static
    {
        return parent::make(...$arguments)
            ->confirmButtonText(__("actions.delete"))
            ->cancelButtonText(__("actions.cancel"))
            ->confirmText(__("actions.sure_delete"));
    }
}
