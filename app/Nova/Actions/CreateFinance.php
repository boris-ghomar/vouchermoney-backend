<?php

namespace App\Nova\Actions;

use App\Models\Customer;
use App\Models\Finance;
use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

class CreateFinance extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    protected $finance_type;

    public function setFinanceType($finance_type)
    {
        $this->finance_type = $finance_type;
        return $this;
    }

    /**
     * Override the name method to dynamically set the button name.
     *
     * @return string
     */
    public function name()
    {
       return ($this->finance_type === 'deposit' ? 'Create Deposit' : 'Create Withdraw');
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $finance = new Finance();

        $finance->amount = $this->finance_type === 'deposit' ? abs($fields->amount) : -abs($fields->amount);
        $finance->customer_id = $fields->customer_id;
        $finance->request_comment = $fields->request_comment;
        $finance->status = $fields->status;

        $finance->save();

        return ActionResponse::message('Created successfully');

    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [

            Number::make('Amount')->rules('required', 'numeric'),
            Select::make('Customer', 'customer_id')
                ->canSee(function () {
                    $user = auth()->user();
                    return $user->hasRole(Role::SUPER_ADMIN);
                })
                ->options(Customer::all()->pluck("name", "id"))
                ->searchable()
                ->rules('required'),
            Hidden::make('Customer','customer_id')->default(function ()
            {
                $user = auth()->user();
                return ($user && $user->customer ? $user->customer->id : null);
            })->canSee(function (){
                return auth()->user()->hasRole(Role::RESELLER) || auth()->user()->hasRole(Role::MERCHANT);
            }),
            Textarea::make('Comment', 'request_comment')->rules('nullable', 'string'),
            Textarea::make('Approved Comment', 'approved_comment')
                ->rules('nullable', 'string')
                ->canSee(function () {
                    $user = auth()->user();
                    return $user->hasRole(Role::SUPER_ADMIN);
                }),

            Hidden::make('Status', 'status')->default(function () {
                $user = auth()->user();
                return ($user && $user->hasRole(Role::SUPER_ADMIN)) ? 'approved' : 'pending';
            }),
            Hidden::make('Resolved By', 'resolved_by')->default(function () {
                $user = auth()->user();
                return ($user && $user->hasRole(Role::SUPER_ADMIN)) ? auth()->id() : null;
            }),
        ];
    }
}
