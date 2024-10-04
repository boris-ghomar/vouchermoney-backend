<?php

namespace App\Nova\Menu;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

trait MenuMacro
{
    public function onlyForAdmins(array $canAny = []): static
    {
        $this->canSee(function (Request $request) use ($canAny) {
            /** @var User $user */
            $user = $request->user();

            if (!$user || !$user->is_admin) return false;

            return $user->is_super || $user->canAny($canAny);
        });

        return $this;
    }

    public function onlyForSuper(): static
    {
        $this->canSee(function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            return ! (!$user || !$user->is_super);
        });

        return $this;
    }

    public function onlyForCustomers(array $canAny = []): static
    {
        $this->canSee(function (Request $request) use ($canAny) {
            /** @var User $user */
            $user = $request->user();

            if (!$user || !$user->is_customer) return false;

            return $user->is_customer_admin || $user->canAny($canAny);
        });

        return $this;
    }

    public function canAny(array $adminAbilities = [], array $customerAbilities = []): static
    {
        $this->canSee(function (Request $request) use ($adminAbilities, $customerAbilities) {
            /** @var User $user */
            $user = $request->user();

            if (!$user) return false;

            if ($user->is_super || ($user->is_admin && $user->canAny($adminAbilities)))
                return true;

            if ($user->is_customer_admin || ($user->is_customer && $user->canAny($customerAbilities)))
                return true;

            return false;
        });

        return $this;
    }

    public function canAnyCustomer(array $abilities = []): static
    {
        $this->canSee(function (Request $request) use ($abilities) {
            /** @var User $user */
            $user = $request->user();

            if (!$user || !$user->is_customer) return false;

            return $user->is_customer_admin || $user->canAny($abilities);
        });

        return $this;
    }

    public function canAnyAdmin(array $abilities = []): static
    {
        $this->canSee(function (Request $request) use ($abilities) {
            /** @var User $user */
            $user = $request->user();

            if (!$user || !$user->is_admin) return false;

            return $user->is_super || $user->canAny($abilities);
        });

        return $this;
    }
}
