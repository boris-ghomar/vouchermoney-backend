<?php

namespace App\Nova\Actions;

use App\Models\Customer;
use App\Models\User;
use App\Nova\Fields\Password;
use App\Nova\Fields\Select;
use App\Nova\Fields\Text;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lednerb\ActionButtonSelector\ShowAsButton;

class CreateCustomer extends Action
{
    use InteractsWithQueue, Queueable, ShowAsButton;

    public $name = "Add";
    public $standalone = true;
    public $confirmText = "";
    public $onlyOnIndex = true;
    public $confirmButtonText = "Add";

    public function authorizedToSee(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user && $user->is_super;
    }

    public function authorizedToRun(Request $request, $model): bool
    {
        return $this->authorizedToSee($request);
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @return ActionResponse
     */
    public function handle(ActionFields $fields): ActionResponse
    {
        /** @var CustomerServiceContract $customerService */
        $customerService = app(CustomerServiceContract::class);
        /** @var ActivityServiceContract $activityService */
        $activityService = app(ActivityServiceContract::class);

        $type = $fields->get("type");
        $name = $fields->get("name");
        $email = $fields->get("email");
        $password = $fields->get("password");

        try {
            /** @var Customer $customer */
            $customer = $customerService->{"make" . ucfirst($type)}($name, $email, $password);
        } catch (Exception $exception) {
            $activityService->novaException($exception, ["fields" => $fields]);
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
