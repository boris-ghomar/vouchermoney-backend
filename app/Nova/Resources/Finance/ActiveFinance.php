<?php

namespace App\Nova\Resources\Finance;

use App\Models\Finance\Finance as Model;
use App\Nova\Actions\ActionHelper;
use App\Nova\Actions\DeleteFinance;
use App\Nova\Actions\RequestFinance;
use App\Nova\Actions\ResolveFinance;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class ActiveFinance extends Finance
{
    public static string $model = Model::class;
    public function actions(NovaRequest $request): array
    {
        return ActionHelper::make([
            RequestFinance::make(),
            DeleteFinance::make(),
            ResolveFinance::make()
        ]);
    }

}
