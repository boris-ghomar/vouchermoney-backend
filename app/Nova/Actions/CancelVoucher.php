<?php

namespace App\Nova\Actions;

use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Exception;

class CancelVoucher extends DestructiveAction
{
    use InteractsWithQueue, Queueable;

    public $confirmButtonText = "Cancel";
    public $name = "Cancel";
    public $confirmText = "Are you sure you want to cancel this voucher?";
    public $showInline = true;

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
            foreach ($models as $model) {
                $model->status = Voucher::STATUS_CANCELED;
                $model->save();
            }
        } catch (Exception $exception) {
            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Voucher successfully cancelled");
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
}
