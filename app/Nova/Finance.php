<?php

namespace App\Nova;

use App\Models\Finance as Model;
use App\Models\Role;
use App\Nova\Actions\CreateFinance;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Customer;
class Finance extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = Model::class;

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
        'customer', 'request_amount', 'status', 'approved_amount', 'created_by'
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        $user = auth()->user();
        if ($user->customer)
        {
            return $query->where('customer_id',$user->customer->id);
        }
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {

        return [
            ID::make()->sortable(),

            Select::make('Customer', 'customer_id')
                ->options(Customer::all()->pluck("name", "id"))
                ->searchable()
                ->rules('required')
                ->canSee(function () {
                    return auth()->user()->hasRole(Role::SUPER_ADMIN);
                })->hideFromIndex(),


            Hidden::make('Customer', 'customer_id')->default(function () {
                $user = auth()->user();
                return ($user && $user->customer) ? $user->customer->id : null;
            })->canSee(function () {
                return auth()->user()->hasRole(Role::RESELLER) || auth()->user()->hasRole(Role::MERCHANT);
            }),
            BelongsTo::make('Customer', 'customer', \App\Nova\Customer  ::class)
                ->onlyOnIndex(),
            Number::make('Amount', 'amount')->rules('required', 'numeric'),

            Textarea::make('Comment', 'request_comment')->rules('nullable', 'string'),

            Textarea::make('Approved Comment', 'approved_comment')
                ->rules('nullable', 'string')
                ->canSee(function () {
                    return auth()->user()->hasRole(Role::SUPER_ADMIN);
                }),

            Badge::make('Status', function () {
                return $this->status;
            })->map([
                'approved' => 'success',
                'rejected' => 'danger',
                'pending' => 'info',
                'canceled' => 'warning',
            ])->withIcons(),

            Hidden::make('Status', 'status')->default(function () {
                return auth()->user()->hasRole(Role::SUPER_ADMIN) ? 'approved' : 'pending';
            }),

            Hidden::make('Resolved By', 'resolved_by')->default(function () {
                return auth()->id();
            }),
        ];
    }


    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */

    public function actions(NovaRequest $request)
    {
        return [
            CreateFinance::make()->setFinanceType('deposit')->standalone()->onlyOnIndex(),
            CreateFinance::make()->setFinanceType('withdraw')->standalone()->onlyOnIndex(),
        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }


}
