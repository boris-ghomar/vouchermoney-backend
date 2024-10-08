<?php

namespace App\Nova\Fields;

use App\Models\User;
use App\Nova\Menu\MenuMacro;
use Illuminate\Http\Request;

trait FieldMacro
{
    use MenuMacro;

    public function onlyForCustomersAdmin(): static
    {
        $this->canSee(function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            if (!$user || !$user->is_customer_admin)
                return false;

            return true;
        });

        return $this;
    }
}
