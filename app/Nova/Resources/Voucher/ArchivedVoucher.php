<?php

namespace App\Nova\Resources\Voucher;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\ArchivedVoucher as Model;
use App\Nova\Customer;
use App\Nova\Fields\Badge;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\Text;
use Illuminate\Http\Request;
use App\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class ArchivedVoucher extends Voucher
{
    public static string $model = Model::class;

    public function fields(NovaRequest $request): array
    {
        $fields = parent::fields($request);

        return array_merge($fields, [
            Badge::make(__("fields.resolved_status"), "state")->filterable()->sortable()
                ->map([Model::STATE_REDEEMED => "success", Model::STATE_EXPIRED => "danger"]),

            Text::make(__("fields.note"), "recipient_note")
                ->onlyOnDetail()->canSee(function (Request $request) {
                    /** @var User $user */
                    $user = $request->user();

                    if (! $user) return false;

                    return $user->can(Permission::CUSTOMERS_VIEW) || ($this->recipient_id && $user->customer_id === $this->recipient_id) ||
                        (empty($this->recipient_id) && $user->customer_id === $this->customer_id);
                }),

            BelongsTo::make("Recipient", "recipient", Customer::class)
                ->onlyForAdmins([Permission::CUSTOMERS_VIEW])->onlyOnDetail(),

            DateTime::make(__("fields.resolved_at"), "resolved_at")->onlyOnDetail()
        ]);
    }
}
