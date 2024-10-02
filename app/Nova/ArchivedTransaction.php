<?php

namespace App\Nova;

use App\Nova\Actions\ActionHelper;
use App\Nova\Fields\DateTime;
use App\Nova\Filters\AmountFilter;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Transaction\ArchivedTransaction as Model;

class ArchivedTransaction extends Transaction
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return array_merge(parent::fields($request), [
            DateTime::make(__("fields.archived_at"), "archived_at")
        ]);
    }
    public function filters(NovaRequest $request): array
    {
        return ActionHelper::make([
            AmountFilter::make()
        ]);
    }
}
