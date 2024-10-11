<?php

namespace App\Providers;

use App\Nova\Resources\Finance\Finance;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use App\Models\Permission;
use App\Models\User;
use App\Models\Finance\Finance as FinanceModel;
use App\Nova\Menu\MenuItem;
use App\Nova\Menu\MenuSection;
use App\Nova\Dashboards\Home;
use App\Nova\ActivityLog;
use App\Nova\CustomerApiToken;
use App\Nova\Resources\Finance\ActiveFinance;
use App\Nova\Resources\Finance\ArchivedFinance;
use App\Nova\Resources\Transaction\ArchivedTransaction;
use App\Nova\Resources\Transaction\ActiveTransactions;
use App\Nova\Resources\User\Account;
use App\Nova\Resources\User\Admin;
use App\Nova\Resources\Voucher\ActiveVoucher;
use App\Nova\Resources\Voucher\ArchivedVoucher;
use App\Nova\Customer;

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

        Nova::userTimezone(fn (Request $request) => $request->user()?->timezone);

        Nova::footer(fn() => null);

        Nova::initialPath("/dashboards/home");

        $this->setUserMenu();

        $this->setMainMenu();
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

    protected function setUserMenu(): void
    {
        Nova::userMenu(function (Request $request, Menu $menu) {
            /** @var User $user */
            $user = $request->user();

            if (!$user) return $menu;

            if ($user->is_customer_admin)
                $menu->prepend(MenuItem::make("Customer", "/resources/customers/$user->customer_id"));

            $menu->prepend(MenuItem::make(__("menu.profile"), "/resources/" . ($user->is_admin ? "admins" : "accounts") . "/$user->id"));

            return $menu;
        });
    }

    protected function setMainMenu(): void
    {
        Nova::mainMenu(function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            $menu = [
                MenuSection::dashboard(Home::class)->icon("home"),
            ];

            if (!$user) return $menu;

            $this->usersMenu($user, $menu);

            $this->customersMenu($user, $menu);

            $this->vouchersMenu($user, $menu);

            $this->financeMenu($user, $menu);

            $this->transactionsMenu($user, $menu);

            $this->activityMenu($user, $menu);

            $this->apiTokenMenu($user, $menu);

            return $menu;
        });
    }

    protected function usersMenu(User $user, array &$menu): void
    {
        if ($user->is_admin)
            $menu[] = MenuSection::resource(Admin::class)->icon('users');
        else if ($user->can(Permission::CUSTOMER_USER_VIEW))
            $menu[] = MenuSection::resource(Account::class)->icon('users');
    }

    protected function customersMenu(User $user, array &$menu): void
    {
        if ($user->can(Permission::CUSTOMERS_VIEW))
            $menu[] = MenuSection::resource(Customer::class)->icon("user-group");
    }

    protected function vouchersMenu(User $user, array &$menu): void
    {
        if ($user->canAny([
            Permission::VOUCHERS_VIEW,
            Permission::CUSTOMER_VOUCHER_FREEZE,
            Permission::CUSTOMER_VOUCHER_GENERATE,
            Permission::CUSTOMER_VOUCHER_VIEW,
            Permission::CUSTOMER_VOUCHER_REDEEM
        ])) {
            $items = [MenuItem::resource(ActiveVoucher::class)];

            if ($user->canAny([Permission::VOUCHERS_VIEW, Permission::CUSTOMER_VOUCHER_VIEW]))
                $items[] = MenuItem::resource(ArchivedVoucher::class);

            $menu[] = MenuSection::make(__("menu.vouchers"), $items)
                ->icon("cash")->collapsedByDefault();
        }
    }

    protected function financeMenu(User $user, array &$menu): void
    {
        if ($user->canAny([Permission::FINANCES_VIEW, Permission::FINANCES_MANAGEMENT, Permission::CUSTOMER_FINANCE])) {
            $section = MenuSection::resource(ActiveFinance::class)->icon(Finance::ICON);

            if ($user->can(Permission::FINANCES_MANAGEMENT))
                $section->withBadgeIf(fn() => FinanceModel::count(), "danger", fn() => FinanceModel::count() > 0);
            else if ($user->can(Permission::CUSTOMER_FINANCE))
                $section->withBadgeIf(
                    fn() => $user->customer->finances()->count(), "info",
                    fn() => $user->customer->finances()->count() > 0
                );

            $menu[] = $section;

            $menu[] = MenuSection::resource(ArchivedFinance::class)->icon("archive");
        }
    }

    protected function transactionsMenu(User $user, array &$menu): void
    {
        if ($user->canAny([Permission::CUSTOMER_TRANSACTIONS_VIEW, Permission::TRANSACTIONS_VIEW])) {
            $menu[] = MenuSection::make(__("menu.transactions"), [
                MenuItem::resource(ActiveTransactions::class),
                MenuItem::resource(ArchivedTransaction::class)
            ])->icon("clipboard-list")->collapsedByDefault();
        }
    }

    protected function activityMenu(User $user, array &$menu): void
    {
        if ($user->can(Permission::ACTIVITY_VIEW))
            $menu[] = MenuSection::resource(ActivityLog::class)->icon('lightning-bolt');
    }

    protected function apiTokenMenu(User $user, array &$menu): void
    {
        if ($user->is_customer_admin) $menu[] = MenuSection::resource(CustomerApiToken::class)->icon('key');
    }
}
