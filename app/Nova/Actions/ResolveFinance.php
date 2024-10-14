<?php

namespace App\Nova\Actions;

use App\Models\Finance\Finance;
use App\Models\Permission;
use App\Models\User;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Finance\Contracts\FinanceServiceContract;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;

class ResolveFinance extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    protected string $type;
    public $confirmText = "";
    public $showInline = true;
    public $onlyOnIndex = true;

    public function approve(): static
    {
        return $this->setType("approve");
    }

    public function reject(): static
    {
        return $this->setType("reject");
    }

    private function setType(string $type): static
    {
        $this->type = $type;
        $this->confirmButtonText = __("actions." . $type);
        $this->cancelButtonText = __("actions.cancel");

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

        return $user && $user->can(Permission::FINANCES_MANAGEMENT);
    }

    /**
     * Override the name method to dynamically set the button name.
     *
     * @return string
     */
    public function name(): string
    {
        return __("actions." . $this->type);
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
        /** @var FinanceServiceContract $financeService */
        $financeService = app(FinanceServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        try {
            foreach ($models as $model) $financeService->{$this->type}($model, $fields->get("comment"));
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["finances" => $models, "fields" => $fields]);
            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Success");
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
            Text::make(__("fields.comment"), "comment")
                ->rules("nullable", "string")
        ];
    }

    public static function make(...$arguments): array
    {
        $approve = parent::make()->approve();
        $reject = parent::make()->reject();
        return [$approve, $reject];
    }
}
