<?php

namespace App\Nova\Actions;

use App\Models\ArchivedFinance;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Finance as Model;
use App\Models\ArchivedFinance as Archived;

class ManageFinance extends Action
{
    use InteractsWithQueue, Queueable;
    public $showInline = true;

    public string $type;

    /**
     * @param $type
     * @return $this
     */
    public function setType($type): static
    {
        $this->type = $type;
        $this->name = ucfirst($type);
        return $this;
    }


    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return ActionResponse
     */

    public function handle(ActionFields $fields,  Collection $models): ActionResponse
    {

        foreach ($models as $model)
        {
            if ($this->type === Model::ACTION_APPROVE) {
                $model->status = Archived::STATUS_APPROVED;
                $model->resolved_comment = $fields->comment;
                $model->resolved_by = auth()->user()->id;
            } elseif ($this->type === Model::ACTION_REJECT) {
                $model->status = Archived::STATUS_REJECTED;
                $model->resolved_comment = $fields->comment;
                $model->resolved_by = auth()->user()->id;
            } elseif ($this->type === Model::ACTION_CANCEL) {

                $model->delete();
                return ActionResponse::message("Finance is canceled successfully");
            }
            ArchivedFinance::create([
                'customer_id' => $model->customer_id,
                'amount' => $model->amount,
                'request_comment' => $model->request_comment,
                'status' => $model->status,
                'resolved_comment' => $model->resolved_comment,
                'resolved_by' => auth()->user()->id,
            ]);
            $model->delete();
        }
        return ActionResponse::redirect('/resources/archived-finances');

    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        if ($this->type === Model::ACTION_APPROVE || $this->type === Model::ACTION_REJECT) {
            return [
                Text::make('Comment', 'resolved_comment')->nullable(),
            ];
        } else {
            return [];
        }
    }
}
