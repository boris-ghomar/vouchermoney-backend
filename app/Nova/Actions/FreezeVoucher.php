<?php

namespace App\Nova\Actions;

use App\Models\Voucher\Voucher;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Exception;

class FreezeVoucher extends DestructiveAction
{
    use ShowAsButton;

    public $showInline = true;
    public $sole = true;

    protected string $type = 'freeze';

    public function activate(): static
    {
        return $this->setType("activate");
    }

    public function freeze(): static
    {
        return $this->setType("freeze");
    }

    private function setType(string $type): static
    {
        $this->type = $type;
//        $this->name = $type === 'freeze' ? __('actions.freeze') : __('actions.activate');
        return $this;
    }

    public function name()
    {
        return $this->type === 'freeze' ? __('actions.freeze') : __('actions.activate');
    }

    public $uriKey = "freeze-voucher";

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @param  ActionFields  $fields
     * @param  Collection  $models
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var Voucher $voucher */
        $voucher = $models->first();
        try {
            if ($voucher->active) $voucher->freeze();
            else $voucher->activate();
        } catch (Exception $exception) {
            return ActionResponse::danger("Something went wrong");
        }

        return ActionResponse::message("Voucher " . ($voucher->active ? "activated" : "frozen"));
    }

    /**
     * Get the fields available on the action.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }

    public static function make(...$arguments): static
    {
        $result = parent::make();
        $confirm = "actions.";
        $description = "actions.";
        if ($arguments[0] && $arguments[0]?->active) {
            $result->freeze();
            $confirm .= 'freeze';
            $description .= 'freeze_description';
        }
        else {
            $result->activate();
            $confirm .= 'activate';
            $description .= 'activate_description';
        }
        return $result->confirmButtonText(__($confirm))
            ->cancelButtonText(__("actions.cancel"))
            ->confirmText(__($description));
    }
}
