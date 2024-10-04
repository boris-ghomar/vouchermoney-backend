<?php

namespace App\Providers;

use App\Models\Finance\Finance as FinanceModel;
use App\Models\Permission;
use App\Models\User;
use App\Nova\Account;
use App\Nova\ActiveVoucher;
use App\Nova\ActivityLog;
use App\Nova\Admin;
use App\Nova\ArchivedTransaction;
use App\Nova\ArchivedVoucher;
use App\Nova\Customer;
use App\Nova\Dashboards\Home;
use App\Nova\Menu\MenuItem;
use App\Nova\Menu\MenuSection;
use App\Nova\Resources\Finance\ArchivedFinance;
use App\Nova\Resources\Finance\Finance;
use App\Nova\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        Nova::footer(fn() => null);

        Nova::initialPath("/dashboards/home");

        Nova::userMenu(function (Request $request, Menu $menu) {
            /** @var User $user */
            $user = $request->user();

            if (!$user) return $menu;

            if ($user->is_customer_admin || $user->can(Permission::CUSTOMER_VIEW))
                $menu->prepend(MenuItem::make(__("menu.customer"), "/resources/customers/{$user->customer_id}"));

            $menu->prepend(MenuItem::make(__("menu.profile"), "/resources/" . ($user->is_admin ? "admins" : "accounts") . "/{$user->id}"));

            return $menu;
        });

        Nova::mainMenu(fn(Request $request) => [
            MenuSection::dashboard(Home::class)->icon("home"),

            MenuSection::resource(Admin::class)->icon('users')
                ->onlyForAdmins(),

            MenuSection::resource(Account::class)->icon('users')
                ->onlyForCustomers([Permission::CUSTOMER_USER_VIEW]),

            MenuSection::resource(Customer::class)->icon("user-group")
                ->onlyForAdmins([Permission::CUSTOMERS_VIEW]),

            MenuSection::make(__("menu.vouchers"), [
                MenuItem::resource(ActiveVoucher::class)->canAny([Permission::VOUCHERS_VIEW], [
                    Permission::CUSTOMER_VOUCHER_FREEZE,
                    Permission::CUSTOMER_VOUCHER_GENERATE,
                    Permission::CUSTOMER_VOUCHER_VIEW,
                    Permission::CUSTOMER_VOUCHER_REDEEM
                ]),
                MenuItem::resource(ArchivedVoucher::class)->canAny(
                    [Permission::VOUCHERS_VIEW], [Permission::CUSTOMER_VOUCHER_VIEW]
                ),
            ])->icon("cash")->canAny([Permission::VOUCHERS_VIEW], [
                Permission::CUSTOMER_VOUCHER_FREEZE,
                Permission::CUSTOMER_VOUCHER_GENERATE,
                Permission::CUSTOMER_VOUCHER_VIEW,
                Permission::CUSTOMER_VOUCHER_REDEEM
            ])->collapsable()->collapsedByDefault(),

            MenuSection::make(__("menu.transactions"), [
                MenuItem::resource(Transaction::class),
                MenuItem::resource(ArchivedTransaction::class)
            ])->icon("clipboard-list")->canAny([Permission::TRANSACTIONS_VIEW], [Permission::CUSTOMER_TRANSACTIONS_VIEW])
                ->collapsable()->collapsedByDefault(),

            MenuSection::resource(Finance::class)
                ->icon(Finance::ICON)->withBadgeIf(
                    fn() => FinanceModel::query()->count(), "danger",
                    fn() => FinanceModel::query()->count() > 0
                )->canAnyAdmin([Permission::FINANCES_VIEW, Permission::FINANCES_MANAGEMENT]),

            MenuSection::resource(ArchivedFinance::class)
                ->icon("archive")->canAnyAdmin([Permission::FINANCES_VIEW]),

            MenuSection::make(__("menu.finances"), [
                MenuItem::resource(Finance::class)
                    ->withBadgeIf(
                        fn() => $request->user()?->customer->finances()->count(), "info",
                        fn() => $request->user()?->customer->finances()->count() > 0
                    ),
                MenuItem::resource(ArchivedFinance::class),
            ])->icon(Finance::ICON)->canAnyCustomer([Permission::CUSTOMER_FINANCE]),

            MenuSection::resource(ActivityLog::class)->icon('lightning-bolt')
                ->onlyForAdmins([Permission::ACTIVITY_VIEW])
        ]);
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes(): void
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate(): void
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards(): array
    {
        return [
            new Home(),
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools(): array
    {
        return [];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
