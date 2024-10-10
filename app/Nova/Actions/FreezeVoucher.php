<?php

namespace App\Nova\Actions;

use App\Models\Permission;
use App\Models\User;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Voucher\Contracts\VoucherServiceContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class FreezeVoucher extends DestructiveAction
{
    use ShowAsButton;

    public $showInline = true;
    public $uriKey = "freeze-voucher";
    public $withoutActionEvents = true;
    protected string $type = 'freeze';
    public $sole = true;

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
        $this->name = $type === 'freeze' ? __('actions.freeze') : __('actions.activate');
        $this->cancelButtonText = __("actions.cancel");
        $this->confirmButtonText = ucfirst($this->name);
        $this->confirmText = __("actions." . $type . "_description");

        return $this;
    }

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user && $user->can(Permission::CUSTOMER_VOUCHER_FREEZE);
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var VoucherServiceContract $voucherService */
        $voucherService = app(VoucherServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        try {
            DB::transaction(function () use ($models, $voucherService) {
                foreach ($models as $model) {
                    if ($this->type === "freeze" && $model->is_active) $voucherService->freeze($model);
                    else if ($this->type === "activate" && $model->is_frozen) $voucherService->activate($model);
                }
            });
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["vouchers" => $models, "type" => $this->type]);
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::message("Success");
    }

    public static function make(...$arguments): static
    {
        return parent::make()->{$arguments[0] && $arguments[0]->active ? "freeze" : "activate"}();
    }
}
