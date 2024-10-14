<?php

namespace App\Nova\Resources\Finance;

use App\Models\Finance\ArchivedFinance as Model;
use App\Nova\Fields\Badge;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\Text;
use App\Nova\Resources\User\User;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class ArchivedFinance extends Finance
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    public function fields(NovaRequest $request): array
    {
        return array_merge(parent::fields($request), FieldHelper::make([
            BelongsTo::make("Resolver", "resolver", User::class)
                ->onlyForAdmins(),

            Badge::make(__("fields.status"), "status")
                ->asBoolean()->withIcons()->sortable()->filterable()
                ->labels(['Rejected', 'Approved']),

            Text::make('Resolver comment', 'resolver_comment')->onlyOnDetail(),

            DateTime::make(__("fields.resolved_at"), "resolved_at")->sortable()->filterable(),
        ]));
    }
}
