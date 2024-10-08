<?php

namespace App\Nova\Resources\Voucher;

use App\Models\Voucher\VoucherActivity as Model;
use App\Nova\Fields\Badge;
use App\Nova\Fields\Code;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\Text;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class VoucherActivity extends Resource
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
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id'];

    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__("fields.id"), "id")->sortable(),

            Text::make(__("fields.code"), "code")->filterable(),

            Badge::make(__("fields.state"), "state")
                ->map([
                    Model::STATE_CREATED => "info",
                    Model::STATE_FROZEN => "warning",
                    Model::STATE_ACTIVATED => "info",
                    Model::STATE_REDEEMED => "success",
                    Model::STATE_EXPIRED => "danger"
                ]),

            Code::make(__("fields.properties"), "properties"),

            Text::make(__("fields.user"), "user_data->name")->onlyOnIndex(),

            Code::make(__("fields.user_data"), "user_data")->json(),

            DateTime::make(__("fields.time"), "time")
                ->filterable()->sortable()
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request): bool
    {
        return false;
    }
}
