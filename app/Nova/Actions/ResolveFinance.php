<?php

namespace App\Nova\Actions;

use App\Models\Finance\Finance;
use App\Models\Permission;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;

class ResolveFinance extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    protected string $type;
    public $confirmText = "";
    public $showInline = true;

    public function approve(): static
    {
        return $this->setType("approve");
    }

    public function reject(): static
    {
        return $this->setType("reject");
    }

    private function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user || $user->is_customer) return false;

        return $user->is_super || $user->can(Permission::FINANCES_MANAGEMENT);
    }

    /**
     * Override the name method to dynamically set the button name.
     *
     * @return string
     */
    public function name(): string
    {
        return __("actions." . $this->type);
    }

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection<Finance>  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $user = auth()->user();

        try {
            foreach ($models as $model) $model->{$this->type}($user, $fields->comment ?: "");
        } catch (Exception $exception) {
            activity(static::class)
                ->withProperties([
                    "user" => $user,
                    "finances" => $models,
                    "fields" => $fields,
                    "exception" => $exception->getMessage()
                ])->log("Exception when attempting to resolve finance");

            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Successfully " . ucfirst($this->type) . "ed");
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
            Text::make(__("fields.comment"), "comment")
        ];
    }

    public static function make(...$arguments): array
    {
        $approve = parent::make()->approve()->confirmButtonText(__("actions.approve"))->cancelButtonText(__("actions.cancel"));
        $reject = parent::make()->reject()->confirmButtonText(__("actions.reject"))->cancelButtonText(__("actions.cancel"));
        return [$approve, $reject];
    }
}
