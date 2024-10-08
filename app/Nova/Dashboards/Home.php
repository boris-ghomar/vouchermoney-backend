<?php

namespace App\Nova\Dashboards;

use App\Models\Permission;
use App\Models\User;
use App\Nova\Metrics\CustomerAvailableBalance;
use Illuminate\Http\Request;
use Laravel\Nova\Dashboard;

class Home extends Dashboard
{
    public $name = "Dashboard";

    public function __construct()
    {
        parent::__construct();

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
            (new CustomerAvailableBalance())->canSee(function (Request $request) {
                /** @var User $user */
                $user = $request->user();

                return $user && ($user->is_customer_admin || $user->can(Permission::CUSTOMER_VIEW));
            }),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'home';
    }
}
