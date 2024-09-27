<?php

namespace App\Nova;

use App\Models\Finance as Model;
use App\Nova\Actions\CreateFinance;
use App\Nova\Actions\ManageFinance;
use Illuminate\Http\Request;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class Finance extends Resource
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
    public static $search = ['id','amount'];

    public function getKey(): string
    {
        return 'finance';
    }

    public static function label(): string
    {
        return 'Finance';
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
            ID::make()->sortable()->canSee(fn(Request $request) => $request->user()?->is_admin),

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
     * @return Action|ActionResponse
     */

    public function actions(NovaRequest $request): array
    {
        return [
            CreateFinance::make()->setType('deposit')->confirmText("")->confirmButtonText('Request Deposit'),
            CreateFinance::make()->setType('withdraw')->confirmText("")->confirmButtonText('Request Withdraw'),
            ManageFinance::make()->setType(Model::ACTION_APPROVE)->canSee(fn(Request  $request) => $request->user()->is_admin)->confirmText("")->confirmButtonText("Approve"),
            ManageFinance::make()->setType(Model::ACTION_REJECT)->canSee(fn(Request  $request) => $request->user()->is_admin)->confirmText("")->confirmButtonText('Reject'),
            ManageFinance::make()->setType(Model::ACTION_CANCEL)->canSee(fn(Request  $request) => $request->user()->is_customer)->confirmText("")->confirmButtonText('Cancel')
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
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
