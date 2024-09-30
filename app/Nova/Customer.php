<?php

namespace App\Nova;

use App\Models\Customer as Model;
use App\Nova\Actions\CreateCustomer;
use App\Nova\Metrics\AccountBalance;
use App\Nova\Metrics\CustomerAvailableBalance;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

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
    public static $search = ['name'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__("fields.id"), "id")->sortable()
                ->canSee(fn (Request $request) => $request->user()?->is_admin),

            Text::make(__("fields.name"), "name")->sortable()->rules("string", "max:100"),

            Avatar::make(__("fields.avatar"), "avatar")->nullable()->disableDownload()
                ->deletable()->prunable()->acceptedTypes('.jpg,.jpeg,.png'),

            Currency::make(__("fields.balance"), "balance")
                ->canSee(fn(Request $request) => $request->user()?->is_admin)
                ->sortable()->filterable(),

            Currency::make(__("fields.balance"), "balance")
                ->canSee(fn(Request $request) => $request->user()?->is_customer),

            Badge::make("Type", "type")->map([
                Model::TYPE_RESELLER => "info",
                Model::TYPE_MERCHANT => "success"
            ])->filterable()->onlyForAdmins(),

            HasMany::make(__("fields.users"), "users", User::class)
                ->collapsable()->collapsedByDefault()->canSee(function (Request $request) {
                    $user = $request->user();
                    return $user && $user->is_admin && $user->can("user:view-any");
                })
        ];
    }

    public function authorizedToDelete(Request $request): bool
    {
        $user = $request->user();
        return $user && $user->is_admin && $user->can("customer:delete");
    }

    public function authorizedToAdd(NovaRequest $request, $model): bool
    {
        $user = $request->user();

        return $user && $user->is_customer && $user->customer_id === $model->id && $user->can("customer:user:create");
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     * @throws HelperNotSupported
     */
    public function cards(NovaRequest $request): array
    {
        return [
            CustomerAvailableBalance::make()->onlyOnDetail()->canSee(fn (Request $request) => $request->user()?->is_admin),
            AccountBalance::make()->onlyOnDetail()->canSee(fn (Request $request) => $request->user()?->is_admin),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
            CreateCustomer::make()
                ->canSee(fn(Request $request) => $request->user()?->is_admin && $request->user()->can("customer:create")),
        ];
    }
}
