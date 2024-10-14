<?php

namespace App\Nova\Actions;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\Voucher;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Voucher\Contracts\VoucherServiceContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class FreezeVoucher extends Action
{
    use ShowAsButton;

    public $showInline = true;
    public $uriKey = "freeze-voucher";
    public $sole = true;
    public $onlyOnIndex = true;
    public $withoutActionEvents = true;
    protected string $type = 'freeze';

    public function activate(): static
    {
        return $this->setType("activate");
    }

    public function freeze(): static
    {
        return $this->setType("freeze");
    }

    public function setType(string $type): static
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
        return $this->authorizedToSee($request) && $request->user()->customer_id === $model->customer_id;
    }

    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var VoucherServiceContract $voucherService */
        $voucherService = app(VoucherServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        try {
            /** @var Voucher $voucher */
            $voucher = $models->first();
            if ($voucher->is_active) $voucherService->freeze($voucher);
            else $voucherService->activate($voucher);
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["vouchers" => $models, "type" => $this->type]);
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::message("Success");
    }

    public static function make(...$arguments): static
    {
        return parent::make()->{$arguments[0]->is_active ? "freeze" : "activate"}();
    }
}
