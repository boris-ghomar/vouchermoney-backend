<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;

class Account extends User
{
    /**
     * @return string
     */
    public static function label(): string
    {
        return __("fields.users");
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $user = $request->user();

        if ($user?->is_customer)
            $query->where("customer_id", $user->customer_id)->whereNot("id", $user?->id);

        return $query;
    }
}
