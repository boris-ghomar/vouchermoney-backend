<?php

namespace App\Nova\Dashboards;

use App\Models\Permission;
use App\Models\User;
use App\Nova\Metrics\AccountBalance;
use Illuminate\Http\Request;
use Laravel\Nova\Dashboard;

class CustomerBalance extends Dashboard
{
    public $name = "Balance";

    public function __construct()
    {
        parent::__construct();

        $this->canSee(function (Request $request) {
            /** @var User $user */
            $user = $request->user();
            if (!$user || $user->is_admin) return false;

            return $user->is_customer_admin || $user->can(Permission::CUSTOMER_VIEW);
        });

        $this->showRefreshButton();
    }

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards(): array
    {
        return [
            new AccountBalance()
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'balance';
    }
}
