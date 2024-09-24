<?php

namespace App\Nova\Actions;

use App\Nova\Customer;
use App\Models\Finance;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Outl1ne\MultiselectField\Multiselect;

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

        $finance = new Finance();

        $finance->amount = abs($fields->amount) * ($this->type === 'deposit' ? 1 : -1);
        $finance->customer_id = $fields->customer_id ?: $authUser->customer_id;
        $finance->request_comment = $fields->request_comment;
        $finance->approved_comment = $fields->approved_comment ?: null;
        $finance->status = $authUser->is_admin ? Finance::STATUS_APPROVED : Finance::STATUS_PENDING;
        $finance->resolved_by = $authUser->is_admin ? $authUser->id : null;
        $finance->save();

        return ActionResponse::message('Created successfully');
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

            Multiselect::make('Customer', 'customer_id')
                ->canSee(fn() => auth()->user()?->is_admin)
                ->singleSelect()
                ->asyncResource(Customer::class)
                ->rules('required'),

            Textarea::make('Comment', 'request_comment')
                ->rules('nullable'),

            Textarea::make('Approved Comment', 'approved_comment')
                ->rules('nullable')
                ->canSee(fn() => auth()->user()?->is_admin),
        ];
    }
}
