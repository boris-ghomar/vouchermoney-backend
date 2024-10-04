<?php

namespace App\Providers;

use App\Models\Finance\Finance as FinanceModel;
use App\Models\Permission;
use App\Models\User;
use App\Nova\ActiveVoucher;
use App\Nova\ActivityLog;
use App\Nova\ArchivedTransaction;
use App\Nova\ArchivedVoucher;
use App\Nova\Customer;
use App\Nova\Dashboards\Home;
use App\Nova\Menu\MenuItem;
use App\Nova\Menu\MenuSection;
use App\Nova\Resources\Finance\ArchivedFinance;
use App\Nova\Resources\Finance\Finance;
use App\Nova\Resources\User\Account;
use App\Nova\Resources\User\Admin;
use App\Nova\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Exceptions\NovaException;
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

        Nova::mainMenu(function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            $menu = [
                MenuSection::dashboard(Home::class)->icon("home"),
            ];

            if (!$user) return $menu;

            return array_merge($menu, $this->{($user->is_admin ? "admin" : "customer") . "Menu"}($user));
        });
    }

    /**
     * @throws NovaException
     */
    private function adminMenu(User $user): array
    {
        $menu = [MenuSection::resource(Admin::class)->icon('users')];

        if ($user->is_super || $user->can(Permission::CUSTOMERS_VIEW))
            $menu[] = MenuSection::resource(Customer::class)->icon("user-group");

        if ($user->is_super || $user->can(Permission::VOUCHERS_VIEW))
            $menu[] = MenuSection::make(__("menu.vouchers"), [
                MenuItem::resource(ActiveVoucher::class),
                MenuItem::resource(ArchivedVoucher::class)
            ])->icon("cash")->collapsedByDefault();

        if ($user->is_super || $user->can(Permission::TRANSACTIONS_VIEW))
            $menu[] = MenuSection::make(__("menu.transactions"), [
                MenuItem::resource(Transaction::class),
                MenuItem::resource(ArchivedTransaction::class)
            ])->icon("clipboard-list")->collapsedByDefault();

        if ($user->is_super || $user->canAny([Permission::FINANCES_VIEW, Permission::FINANCES_MANAGEMENT])) {
            $section = MenuSection::resource(Finance::class)->icon(Finance::ICON);

            if ($user->is_super || $user->can(Permission::FINANCES_MANAGEMENT))
                $section->withBadgeIf(fn() => FinanceModel::count(), "danger", fn() => FinanceModel::count() > 0);

            $menu[] = $section;

            $menu[] = MenuSection::resource(ArchivedFinance::class)->icon("archive");
        }

        if ($user->is_super || $user->can(Permission::ACTIVITY_VIEW))
            $menu[] = MenuSection::resource(ActivityLog::class)->icon('lightning-bolt');

        return $menu;
    }

    private function customerMenu(User $user): array
    {
        $menu = [];

        if ($user->is_customer_admin || $user->can(Permission::CUSTOMER_USER_VIEW))
            $menu[] = MenuSection::resource(Account::class)->icon('users');

        if ($user->is_customer_admin || $user->canAny([
            Permission::CUSTOMER_VOUCHER_FREEZE,
            Permission::CUSTOMER_VOUCHER_GENERATE,
            Permission::CUSTOMER_VOUCHER_VIEW,
            Permission::CUSTOMER_VOUCHER_REDEEM
        ])) {
            $items = [MenuItem::resource(ActiveVoucher::class)];

            if ($user->is_customer_admin || $user->can(Permission::CUSTOMER_VOUCHER_VIEW))
                $items[] = MenuItem::resource(ArchivedVoucher::class);

            $menu[] = MenuSection::make(__("menu.vouchers"), $items)
                ->icon("cash")->collapsedByDefault();
        }

        if ($user->is_customer_admin || $user->can(Permission::CUSTOMER_TRANSACTIONS_VIEW)) {
            $menu[] = MenuSection::make(__("menu.transactions"), [
                MenuItem::resource(Transaction::class),
                MenuItem::resource(ArchivedTransaction::class)
            ])->icon("clipboard-list")->collapsedByDefault();
        }

        if ($user->is_customer_admin || $user->can(Permission::CUSTOMER_FINANCE)) {
            $menu[] = MenuSection::make(__("menu.finances"), [
                MenuItem::resource(Finance::class)
                    ->withBadgeIf(
                        fn() => $user->customer->finances()->count(), "info",
                        fn() => $user->customer->finances()->count() > 0
                    ),
                MenuItem::resource(ArchivedFinance::class),
            ])->icon(Finance::ICON)->collapsedByDefault();
        }

        return $menu;
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
