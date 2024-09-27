<?php

namespace App\Nova\Actions;

use App\Models\Customer;
use App\Models\Finance;
use App\Nova\ArchivedFinance;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;

class CreateFinance extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    protected string $type;

    public $standalone = true;
    public $onlyOnIndex = true;

    public function setType($type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Override the name method to dynamically set the button name.
     *
     * @return string
     */
    public function name(): string
    {
       return $this->type === 'deposit' ? 'Request deposit' : 'Request withdraw';
    }

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $authUser = auth()->user();

        if (!$authUser) return ActionResponse::danger("Something went wrong");

        $amount = abs($fields->amount) * ($this->type === 'deposit' ? 1 : -1);
        $customer_id = $fields->customer_id ?: $authUser->customer_id;

        if ($authUser->is_admin) {
            $archived = new \App\Models\ArchivedFinance();
            $archived->amount = $amount;
            $archived->customer_id = $customer_id;
            $archived->resolved_comment = $fields->comment;
            $archived->status = \App\Models\ArchivedFinance::STATUS_APPROVED;
            $archived->resolved_by = $authUser->id;
            $archived->save();
            return ActionResponse::redirect('/resources/archived-finances');
        }
        else {
            $finance = new Finance();
            $finance->amount = $amount;
            $finance->customer_id = $customer_id;
            $finance->request_comment = $fields->comment;
            $finance->save();
            return ActionResponse::message("Created successfully");
        }

    }

    /**
     * Get the fields available on the action.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Currency::make('Amount')->rules('required'),

            Select::make('Customer', 'customer_id')
                ->canSee(fn() => auth()->user()?->is_admin)
                ->rules(auth()->user()?->is_admin ? 'required' : 'nullable')
                ->options(function () {
                    return Customer::all()->pluck('name','id');
                })
            ->searchable(),

            Text::make('Comment', 'comment')->rules('nullable'),
        ];
    }
}
