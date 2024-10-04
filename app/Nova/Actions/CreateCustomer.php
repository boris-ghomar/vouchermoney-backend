<?php

namespace App\Nova\Actions;

use App\Models\Customer\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Exception;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;
use Illuminate\Validation\Rules\Password as PasswordRule;

class CreateCustomer extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public function name(): string
    {
        return "Add";
    }

    public $standalone = true;

    public $confirmText = "";

    public $onlyOnIndex = true;

    public $confirmButtonText = "Add";

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @return ActionResponse
     */
    public function handle(ActionFields $fields): ActionResponse
    {
        try {
            /** @var Customer $customer */
            $customer = Customer::{"make" . ucfirst($fields->type)}($fields->name, $fields->email, $fields->password);
        } catch (Exception $exception) {
            return ActionResponse::danger($exception->getMessage());
        }

        return ActionResponse::visit("/resources/customers/" . $customer->id);
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
                ->rules('required', PasswordRule::default(), "min:8"),

            Select::make(__("fields.type"), "type")
                ->options([
                    Customer::TYPE_MERCHANT => __("fields." . Customer::TYPE_MERCHANT),
                    Customer::TYPE_RESELLER => __("fields." . Customer::TYPE_RESELLER)
                ])
                ->rules('required'),
        ];
    }
}
