<?php

namespace App\Nova;

use App\Nova\Fields\Badge;
use App\Nova\Fields\Code;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\Text;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Voucher\VoucherActivity as Model;

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

            Badge::make(__("fields.from_state"), "from_state")
                ->map([
                    Model::STATE_CREATED => "info",
                    Model::STATE_ACTIVE => "success",
                    Model::STATE_FROZEN => "warning"
                ]),

            Badge::make(__("fields.to_state"), "to_state")
                ->map([
                    Model::STATE_ACTIVE => "success",
                    Model::STATE_FROZEN => "warning",
                    Model::STATE_REDEEMED => "info",
                    Model::STATE_EXPIRED => "danger",
                ]),

            Text::make(__("fields.description"), "description"),

            Text::make(__("fields.user"), function () {
                return $this->user?->full_name;
            })->onlyOnIndex(),

            Code::make(__("fields.user_data"), "user_data")->json(),

            DateTime::make(__("fields.time"), "time")
                ->filterable()->sortable()
        ];
    }

    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()?->can("voucher:view") ?: false;
    }

    public function authorizedToView(Request $request): bool
    {
        return static::authorizedToViewAny($request);
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
