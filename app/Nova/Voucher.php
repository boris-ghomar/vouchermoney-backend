<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Voucher as Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Model
 */
class Voucher extends Resource
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
    public static $title = 'code';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'code',
    ];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $user = $request->user();

        if ($user && $user->is_admin && $request->viaResource === "customers") {
            $query->where("created_by", $request->viaResourceId);
        } else if ($user && $user->is_customer && $user->customer_id) {
            $query->where("created_by", $user->customer_id);
        }

        return $query;
    }


    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     * @throws HelperNotSupported
     */
    public function fields(NovaRequest $request): array
    {
        return self::getVoucherDefaultFields();
    }

    /**
     * @throws HelperNotSupported
     */
    public static function getVoucherDefaultFields(bool $showStatus = true): array
    {
        $fields = [
            ID::make()->sortable()->canSee(fn(Request $request) => $request->user()?->is_admin),

            Text::make("Code", "code")->copyable()->readonly(),

            Currency::make("Amount", "amount")
        ];

        if ($showStatus) {
            $fields[] = Badge::make('Status', "status")->map([
                Model::STATUS_ACTIVE => 'success',
                Model::STATUS_BLOCKED => 'danger',
                Model::STATUS_CANCELED => 'info',
                Model::STATUS_TRANSFERRED => 'info',
                Model::STATUS_EXPIRED => 'danger',
            ])->withIcons();
        }

        return $fields;
    }

    public function authorizedToUpdate(Request $request): bool
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
        return [];
    }

    public function getKey(): string
    {
        return "all-vouchers";
    }

    public static function uriKey(): string
    {
        return "all-vouchers";
    }
}
