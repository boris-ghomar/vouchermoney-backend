<?php

namespace App\Nova\Actions;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class FreezeVoucher extends DestructiveAction
{
    use ShowAsButton;

    public $showInline = true;

    protected string $type = 'freeze';

    public function activate(): static
    {
        return $this->setType("activate");
    }

    public function freeze(): static
    {
        return $this->setType("freeze");
    }

    private function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function name(): string
    {
        return $this->type === 'freeze' ? __('actions.freeze') : __('actions.activate');
    }

    public $uriKey = "freeze-voucher";

    public $withoutActionEvents = true;

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user && $user->canCustomer(Permission::CUSTOMER_VOUCHER_FREEZE);
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection<Voucher>  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var User $user */
        $user = auth()->user();

        try {
            foreach ($models as $model) {
                if ($this->type === "freeze" && $model->is_active) $model->freeze();
                else if ($this->type === "activate" && $model->is_frozen) $model->activate();
            }
        } catch (Exception $exception) {
            activity(static::class)
                ->causedBy($user)
                ->withProperties([
                    "user" => $user,
                    "fields" => $fields,
                    "vouchers" => $models,
                    "exception" => $exception->getMessage(),
                    "type" => $this->type
                ])->log("Something went wrong");
            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Voucher " . ($this->type === "activate" ? "activated" : "frozen"));
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
        $result = parent::make();
        $confirm = "actions.";
        $description = "actions.";

        $method = $arguments[0] && $arguments[0]->active ? "freeze" : "activate";

        $result->{$method}();
        $confirm .= $method;
        $description .= $method . '_description';

        return $result->confirmButtonText(__($confirm))
            ->cancelButtonText(__("actions.cancel"))
            ->confirmText(__($description));
    }
}
