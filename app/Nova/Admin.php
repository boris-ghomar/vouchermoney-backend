<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;

class Admin extends User
{
    /**
     * @return string
     */
    public static function label(): string
    {
        return __("fields.admins");
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query->whereNull("customer_id")->whereNot("id", $request->user()?->id);
    }
}
