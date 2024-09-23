<?php

namespace App\Nova\Actions;

use App\Models\Permission;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use App\Models\User;
use App\Models\Customer;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Illuminate\Validation\Rules\Password as PasswordRule;

class CreateCustomer extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public function name(): string
    {
        return __("actions.create_customer");
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @return Action|ActionResponse
     */
    public function handle(ActionFields $fields): ActionResponse|Action
    {
        $customer = new Customer();
        $customer->name = $fields->name;
        $customer->type = $fields->type;
        $customer->save();

        $user = new User();
        $user->name = "Admin";
        $user->email = $fields->email;
        $user->customer_id = $customer->id;
        $user->password = $fields->password;
        $user->save();

        $user->syncPermissions(Permission::getCustomerPermissions());

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
            Text::make(__("fields.name"), "name")
                ->rules("required", "max:100", "unique:customers,name"),

            Text::make(__("fields.email"), "email")
                ->rules('required', 'email:dns', 'unique:users,email'),

            Password::make(__("fields.password"), "password")
                ->rules('required', PasswordRule::default()),

            Select::make(__("fields.type"), "type")
                ->options([
                    Customer::TYPE_MERCHANT => __("fields." . Customer::TYPE_MERCHANT),
                    Customer::TYPE_RESELLER => __("fields." . Customer::TYPE_RESELLER)
                ])
                ->rules('required'),
        ];
    }
}
