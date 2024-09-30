<?php

namespace App\Nova;

use App\Models\Finance\ArchivedFinance as Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ArchivedFinance extends Resource
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
    public static $search = [
        'id','customer_id','amount','status','resolved_by'
    ];

    public function getKey(): string
    {
        return 'archived-finance';
    }

    public static function label(): string
    {
        return "Archived Finances";
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $user = $request->user();

        if ($user?->is_customer)
            $query->where("customer_id", $user->customer_id);

        return $query;
    }

    public static $polling = true;

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Customer', 'customer', Customer::class)
                ->canSee(fn(Request $request) => $request->user()?->is_admin),

            Badge::make('Type', function () {
                return $this->amount < 0 ? 'Withdraw' : 'Deposit';
            })->map([
                'Withdraw' => 'danger',
                'Deposit' => 'success',
            ]),

            Currency::make('Amount', function ($amount) {
                return abs($amount->amount);
            }),

            Text::make('Request comment', 'request_comment')->onlyOnDetail(),

            Badge::make('Status', function () {
                return $this->status;
            })->map([
                'approved' => 'success',
                'rejected' => 'danger',
            ])->withIcons(),

            Text::make('Resolver comment', 'resolved_comment')->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
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
        return [];
    }

   public static function authorizedToCreate(Request $request): false
   {
       return false;
   }

   public function authorizedToUpdate(Request $request): false
   {
       return false;
   }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }
    public function authorizedToDelete(Request $request): false
    {
        return false;
    }
}
