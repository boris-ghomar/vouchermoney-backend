<?php

namespace App\Nova\Resources\Voucher;

use App\Models\Voucher\Voucher as Model;
use App\Nova\Actions\FreezeVoucher;
use App\Nova\Actions\GenerateVoucher;
use App\Nova\Actions\RedeemVoucher;
use App\Nova\Actions\ViewVoucher;
use App\Nova\Fields\Badge;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class ActiveVoucher extends Voucher
{
    public static string $model = Model::class;

    public function fields(NovaRequest $request): array
    {
        $fields = parent::fields($request);

        return array_merge($fields, [
            Badge::make(__("fields.status"), "active")
                ->map(["info", "success"])->labels([
                    __("fields.frozen"),
                    __("fields.active")
                ])->filterable()->sortable()->exceptOnForms(),
        ]);
    }

    public function actions(NovaRequest $request): array
    {
        return [
            RedeemVoucher::make(),
            GenerateVoucher::make(),
            FreezeVoucher::make($this->resource),
            ViewVoucher::make()
        ];
    }
}
