<?php

namespace App\Nova\Actions;

use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class UseVoucher extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $name = "Use voucher";

    public $standalone = true;

    public $confirmButtonText = "Use";
    public $confirmText = "";

    public $onlyOnIndex = true;

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->is_customer || !$user->customer_id) {
                throw new Exception();
            }

            $voucher = Voucher::query()->where("code", $fields->code)->first();
            $voucher->status = Voucher::STATUS_TRANSFERRED;
            $voucher->used_by = $user->customer_id;
            $voucher->save();
        } catch (Exception $exception) {
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::message("");
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
            Text::make("Voucher code", "code")
                ->rules("required", "exists:vouchers")
        ];
    }
}
