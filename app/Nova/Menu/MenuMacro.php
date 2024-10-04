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

            return $user && $user->is_admin && ($user->is_super || empty($canAny) || $user->canAny($canAny));
        });

        return $this;
    }

    public function onlyForSuper(): static
    {
        $this->canSee(function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            return $user && $user->is_super;
        });

        return $this;
    }

    public function onlyForCustomers(array $canAny = []): static
    {
        $this->canSee(function (Request $request) use ($canAny) {
            /** @var User $user */
            $user = $request->user();

            return $user && $user->is_customer && ($user->is_customer_admin || empty($canAny) || $user->canAny($canAny));
        });

        return $this;
    }

    public function onlyForCustomerAdmin(): static
    {
        $this->canSee(function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            return $user && $user->is_customer_admin;
        });

        return $this;
    }

    public function canAny(array $adminAbilities = [], array $customerAbilities = []): static
    {
        $this->canSee(function (Request $request) use ($adminAbilities, $customerAbilities) {
            /** @var User $user */
            $user = $request->user();

            return $user && (
                $user->is_super ||
                ($user->is_admin && (empty($adminAbilities) || $user->canAny($adminAbilities))) ||
                $user->is_customer_admin ||
                ($user->is_customer && (empty($customerAbilities) || $user->canAny($customerAbilities)))
            );
        });

        return $this;
    }
}
