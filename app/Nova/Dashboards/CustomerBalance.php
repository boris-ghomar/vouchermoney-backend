<?php

namespace App\Nova\Dashboards;

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
            $user = $request->user();
            return $user && $user->is_customer && $user->can("customer:view-balance");
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
