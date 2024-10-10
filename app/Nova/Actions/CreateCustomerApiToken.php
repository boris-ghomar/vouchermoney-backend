<?php

namespace App\Nova\Actions;

use App\Models\CustomerApiToken;
use App\Models\Permission;
use App\Models\User;
use App\Nova\Fields\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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

    public function name(): string
    {
        return "Generate token";
    }

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
        $customer = auth()->user()->customer;
        $token = Str::random(64);

        $hashedToken = hash('sha256', $token);

        $expiresAt = match ($fields->expires_at) {
            'custom' => $fields->get('select_expires_at'),
            'no_expiration' => null,
            default => now()->addDays(+$fields->expires_at),
        };

        $customerApiToken = CustomerApiToken::create([
            'customer_id' =>$customer->id,
            'name' => $fields->name,
            'token' => $hashedToken,
            'expires_at' => $expiresAt,
        ]);

        $selectedPermissions = collect($fields->permissions)->filter(fn ($value) => $value === true)->keys()->toArray();

        if ($selectedPermissions) $customerApiToken->permissions()->attach($selectedPermissions, ['model_type' => CustomerApiToken::class]);

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
        $voucherPermissions = Permission::getApiTokenPermissions();

        $options = [];
        foreach ($voucherPermissions as $details) {
            $options[$details["id"]] = $details['label'] .  " - ". $details['short_description'];
        }
        return [
            Text::make('Name', 'name')->rules('required'),
            BooleanGroup::make('Permissions')
                ->options($options)
                ->rules('required'),
            Select::make('Expires At','expires_at')->options([
                '7' => '7 Days',
                '30' => '30 Days',
                '60' => '60 Days',
                '90' => '90 Days',
                'custom' => 'Custom Date',
                'no_expiration' => 'No Expiration',
            ])->rules('required')->displayUsingLabels(),
            DependencyContainer::make([
                Date::make('Select Expiration Date', 'select_expires_at')
                    ->rules('nullable', 'date')
            ])->dependsOn('expires_at', 'custom'),
        ];
    }
}
