<?php

namespace App\Nova\Fields;

use Illuminate\Http\Request;

trait FieldMacro
{
    public function onlyForAdmins(): static
    {
        $this->canSee(fn(Request $request) => $request->user()?->is_admin);

        return $this;
    }

    public function onlyForCustomers(): static
    {
        $this->canSee(fn(Request $request) => $request->user()?->is_customer);

        return $this;
    }

    public function seeIfCan(...$abilities): static
    {
        $this->canSee(function (Request $request) use ($abilities) {
            return $request->user()?->can(...$abilities);
        });

        return $this;
    }

    public function seeIfCanAny(array $abilities): static
    {
        $this->canSee(function (Request $request) use ($abilities) {
            return $request->user()?->canAny(...$abilities);
        });

        return $this;
    }
}
