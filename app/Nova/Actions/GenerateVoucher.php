<?php

namespace App\Nova\Actions;

use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class GenerateVoucher extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $onlyOnIndex = true;

    public $standalone = true;

    public $name = "Generate voucher";

    public $confirmButtonText = "Generate";

    public $modalSize = "3xl";

    public $confirmText = "";

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $service = new VoucherService();

        $user = auth()->user();

        if (!$user || !$user->is_customer || !$user->customer_id) {
            return ActionResponse::danger("Customer is not found");
        }

        $count = $fields->count ?: 1;

        try {
            for ($i = 0; $i < $count; $i++) {
                $voucher = new Voucher();
                $voucher->code = $service->generateCode();
                $voucher->amount = $fields->amount;
                $voucher->created_by = $user->customer_id;
                $voucher->save();
            }
        } catch (Exception $exception) {
            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Voucher" . ($count === 1 ? "" : "s") . " generated");
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
            Number::make("How many vouchers?", "count")
                ->rules("required")->default(1),

            Currency::make("Amount", "amount")
                ->rules("required")
        ];
    }
}
