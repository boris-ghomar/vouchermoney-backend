<?php

namespace App\Nova;

use App\Nova\Actions\CreateCustomer;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Customer as Model;
use App\Models\Role as RoleModel;

/**
 * @mixin Model
 */
class Customer extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make("Name")->filterable()->sortable(),
            Avatar::make("Avatar")->nullable()->disableDownload()->deletable()->prunable()->acceptedTypes('.jpg,.jpeg,.png'),
            BelongsTo::make("User", "user", User::class)->hideFromIndex(),
            Currency::make("Balance")->sortable()->filterable(),

            Number::make("Max vouchers count", "max_vouchers_count")->hideFromIndex(),
            Number::make("Max voucher amount", "max_voucher_amount")->hideFromIndex(),

            Badge::make("Role", function () {
                return $this->user->role->name;
            })->map([
                RoleModel::SUPER_ADMIN => "danger",
                RoleModel::MERCHANT => "info",
                RoleModel::RESELLER => "success"
            ])->onlyOnIndex(),

            HasMany::make("Accounts", "children", User::class),

        ];
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    /**
     * Get the cards available for the request.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
            CreateCustomer::make()->standalone()->onlyOnIndex(),
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }
}
