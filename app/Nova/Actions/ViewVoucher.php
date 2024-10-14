<?php

namespace App\Nova\Actions;

use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Nova\Fields\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;

class ViewVoucher extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $standalone = true;
    public $onlyOnIndex = true;

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $code = $fields->get("code");

        $voucher = Voucher::findByCode($code);

        if (empty($voucher)) {
            $voucher = ArchivedVoucher::findByCode($code);
        }

        if (empty($voucher)) {
            return ActionResponse::danger("Voucher not found");
        }

        return ActionResponse::redirect("/resources/" . ($voucher instanceof ArchivedVoucher ? "archived" : "active") . "-vouchers/" . $voucher->id);
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
        ];
    }
}
