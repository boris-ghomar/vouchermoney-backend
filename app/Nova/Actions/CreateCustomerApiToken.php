<?php

namespace App\Nova\Actions;

use App\Exceptions\AttemptToCreateExpiredApiToken;
use App\Models\CustomerApiToken;
use App\Models\Permission;
use App\Models\User;
use App\Nova\Fields\Text;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Exception;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Date;
use Outl1ne\DependencyContainer\DependencyContainer;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;

class CreateCustomerApiToken extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $standalone = true;
    public $onlyOnIndex = true;
    public $name = "Generate token";

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) return false;

        return $user->is_customer_admin;
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (empty($user)) {
            return ActionResponse::danger("Not authorized");
        }

        $customer = $user->customer;

        if (empty($customer)) {
            return ActionResponse::danger("Not authorized");
        }

        /** @var CustomerServiceContract $customerService */
        $customerService = app(CustomerServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        $expires_at = $fields->get("expires_at");

        $expires_at = match ($expires_at) {
            'custom' => $fields->get('select_expires_at'),
            'no_expiration' => null,
            default => now()->addDays(+$expires_at),
        };

        $name = $fields->get("name");
        $permissions = collect($fields->get("permissions"))->filter(fn ($value) => $value === true)->keys()->toArray();

        try {
            $token = $customerService->createApiToken($customer, $name, $permissions, $expires_at);
        } catch (AttemptToCreateExpiredApiToken $exception) {
            $activityService->novaException($exception);
            return ActionResponse::danger("Expiration time must be greater that current time");
        } catch (Exception $exception) {
            $activityService->novaException($exception);
            return ActionResponse::danger($exception->getMessage());
        }

       return ActionResponse::modal('api-token-modal', [
           "message" => "Please copy token for future use, as you won't be able to view it.",
           "token" => $token
       ]);
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Name', 'name')->rules('required', "string"),

            BooleanGroup::make('Permissions')
                ->options(Permission::getApiTokenPermissions())
                ->rules('required'),

            Select::make('Expiration','expires_at')->options([
                '7' => '7 Days',
                '30' => '30 Days',
                '60' => '60 Days',
                '90' => '90 Days',
                'custom' => 'Custom Date',
                'no_expiration' => 'No Expiration',
            ])->rules('required')->displayUsingLabels(),

            DependencyContainer::make([
                Date::make('Select Expiration Date', 'select_expires_at')
                    ->rules('nullable', 'date', "after:today")
            ])->dependsOn('expires_at', 'custom'),
        ];
    }
}
