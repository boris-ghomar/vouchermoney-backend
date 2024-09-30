<?php

namespace App\Nova\Actions;

use App\Models\Voucher\Voucher;
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
    public $sole = true;

    public function name(): string
    {
        return __("actions.freeze");
    }

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var Voucher $voucher */
        $voucher = $models->first();

        try {
            if ($voucher->active) {

                $voucher->freeze();
            } else {
                $voucher->activate();
            }
        } catch (Exception $exception) {
            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Voucher frozen");
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
