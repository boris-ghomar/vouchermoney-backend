<?php

namespace App\Nova\Actions;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\Voucher;
use App\Nova\Fields\Text;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class RedeemVoucher extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $standalone = true;
    public $confirmText = "";
    public $onlyOnIndex = true;

    public function name(): string
    {
        return __("actions.redeem");
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user && $user->can(Permission::CUSTOMER_VOUCHER_REDEEM);
    }

    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $code = $fields->get("code");
        $note = $fields->get("note");

        $voucher = Voucher::findByCode($code);

        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        /** @var CustomerServiceContract $customerService */
        $customerService = app(CustomerServiceContract::class);

        if (empty($voucher)) {
            $activityService->activity("attempt:nova:redeem-voucher", "Attempt to redeem voucher that not exists", ["fields" => $fields]);
            return ActionResponse::danger("Voucher not found, not active or already used");
        }

        if (! $voucher->active) {
            $activityService->activity(
                "attempt:nova:redeem-voucher",
                "Attempt to redeem voucher that frozen",
                ["fields" => $fields, "voucher" => $voucher]
            );

            return ActionResponse::danger("Voucher not found, not active or already used");
        }

        try {
            $customerService->redeemVoucher($user->customer, $voucher, $note);
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["fields" => $fields, "voucher" => $voucher]);
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::message("Voucher successfully redeemed");
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
            Text::make(__("fields.code"), "code")
                ->rules("required"),

            Text::make(__("fields.note"), "note")
                ->rules("nullable", "string", "max:200")
        ];
    }

    public static function make(...$arguments): static
    {
        return parent::make()
            ->confirmButtonText(__("actions.redeem"))
            ->cancelButtonText(__("actions.cancel"));
    }
}
