<?php

namespace App\Nova;

use App\Models\Voucher as Model;
use App\Nova\Actions\CancelVoucher;
use App\Nova\Actions\GenerateVoucher;
use App\Nova\Actions\UseVoucher;
use App\Nova\Lenses\ResolvedVouchers;
use App\Nova\Lenses\UsedVouchers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class ActiveVoucher extends Voucher
{
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $query->where("status", Model::STATUS_ACTIVE);

        return parent::indexQuery($request, $query);
    }

    public static function label(): string
    {
        return "Vouchers";
    }

    public function actions(NovaRequest $request): array
    {
        return [
            GenerateVoucher::make()->canSee(fn(Request $request) => $request->user()?->is_customer),
            UseVoucher::make()->canSee(fn(Request $request) => $request->user()?->is_customer),
            CancelVoucher::make(),
        ];
    }

    public static function uriKey(): string
    {
        return "vouchers";
    }

    public function lenses(NovaRequest $request): array
    {
        return [
            ResolvedVouchers::make(),
            UsedVouchers::make()
        ];
    }
}
