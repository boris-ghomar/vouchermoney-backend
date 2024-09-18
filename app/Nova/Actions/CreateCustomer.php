<?php

namespace App\Nova\Actions;

use \App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Hash;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\PasswordConfirmation;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use App\Models\User;
use App\Models\Customer;
use Laravel\Nova\Http\Requests\NovaRequest;

class CreateCustomer extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Create Customer';

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @return Action|ActionResponse
     */
    public function handle(ActionFields $fields): ActionResponse|Action
    {
        $user = new User();
        $user->name = "Super admin";
        $user->email = $fields->email;
        $user->password = Hash::make($fields->password);

        $user->save();


        $role = Role::find($fields->role);
        if ($role) {
            $user->roles()->attach($role->id);
        }
        $customer = new Customer();
        $customer->user_id = $user->id;
        $customer->name = $fields->name;
        $customer->save();

        return Action::visit("/resources/customers/" . $customer->id);
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
            Text::make('Name')
                ->rules("required", "max:255", "unique:customers,name"),

            Text::make('Email')
                ->rules('required', 'email:dns', 'unique:users,email'),

            Password::make('Password')
                ->rules('required', 'min:8', 'confirmed'),

            PasswordConfirmation::make('Password confirmation')->rules("required"),

            Select::make('Role')
                ->options([
                    'reseller' => 'Reseller',
                    'merchant' => 'Merchant',
                ])
                ->rules('required'),
        ];
    }
}
