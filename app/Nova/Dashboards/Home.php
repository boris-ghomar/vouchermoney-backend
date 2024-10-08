<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\AccountBalance;
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
                return $request->user()?->is_customer && $request->user()?->can("customer:view-balance");
            }),
            (new AccountBalance())->canSee(function (Request $request) {
                return $request->user()?->is_customer && $request->user()?->can("customer:view-balance");
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
