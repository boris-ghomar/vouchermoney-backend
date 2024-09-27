<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class ArchivedFinance extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ArchivedFinance>
     */
    public static string $model = \App\Models\ArchivedFinance::class;

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

            Textarea::make('Request comment', 'request_comment')->onlyOnDetail(),

            Badge::make('Status', function () {
                return $this->status;
            })->map([
                'approved' => 'success',
                'rejected' => 'danger',
            ])->withIcons(),

            Textarea::make('Resolved comment', 'resolved_comment')->onlyOnDetail(),

            BelongsTo::make("Resolved by", "resolver", User::class)
                ->canSee(fn(Request $request) => $request->user()?->is_admin),
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

   public static function authorizedToCreate(Request $request)
   {
       return false;
   }

   public function authorizedToUpdate(Request $request)
   {
       return false;
   }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }
    public function authorizedToDelete(Request $request)
    {
        return false;
    }
}
