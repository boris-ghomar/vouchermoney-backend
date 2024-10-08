<?php

namespace App\Providers;

use App\Nova\Account;
use App\Nova\ActiveVoucher;
use App\Nova\ActivityLog;
use App\Nova\Admin;
use App\Nova\ArchivedTransaction;
use App\Nova\ArchivedVoucher;
use App\Nova\ArchivedFinance;
use App\Nova\Customer;
use App\Nova\CustomerApiToken;
use App\Nova\Dashboards\Home;
use App\Nova\Finance;
use App\Nova\PersonalAccessToken;
use App\Nova\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use App\Models\Finance\Finance as FinanceModel;

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

        Nova::userTimezone(fn(Request $request) => $request->user()?->timezone ?: config("app.timezone"));

        $can = fn(...$permissions) => fn(NovaRequest $request) => $request->user()?->canAny(...$permissions);

        Nova::userMenu(function (Request $request, Menu $menu) {
            if ($request->user()?->is_customer && $request->user()->can("customer:view-balance"))
                $menu->prepend(
                    MenuItem::make(
                        'Customer',
                        "/resources/customers/{$request->user()->customer_id}"
                    ),
                );

            $menu->prepend(
                MenuItem::make(
                    'Profile',
                    "/resources/users/{$request->user()?->getKey()}"
                ),
            );

            return $menu;
        });

        Nova::mainMenu(fn(Request $request) => [
            MenuSection::dashboard(Home::class)->icon("home"),

            MenuSection::resource(Admin::class)->icon('users')
                ->canSee($can(["user:view-any", "user:create"])),

            MenuSection::resource(Account::class)->icon('users')
                ->canSee($can(["customer:user:view-any","customer:user:create"])),

            MenuSection::resource(Customer::class)->icon("user-group")
                ->canSee($can(["customer:view-any"])),

            MenuSection::make("Vouchers", [
                MenuItem::resource(ActiveVoucher::class)->canSee($can(["voucher:view", "customer:voucher:view", "customer:voucher:generate", "customer:voucher:redeem", "customer:voucher:freeze"])),
                MenuItem::resource(ArchivedVoucher::class)->canSee($can(["voucher:view", "customer:voucher:view"])),
            ])->icon("cash")->canSee($can(["voucher:view", "customer:voucher:view", "customer:voucher:generate", "customer:voucher:redeem", "customer:voucher:freeze"])),

            MenuSection::make("Transactions", [
                MenuItem::resource(Transaction::class),
                MenuItem::resource(ArchivedTransaction::class)
            ])->icon("clipboard-list")->canSee($can(["transaction:view", "customer:transaction:view"])),

            MenuSection::make("Finance", [
                MenuItem::resource(Finance::class)->withBadgeIf(function () use ($request) {
                    $user = $request->user();

                    if ($user->is_admin) $count = FinanceModel::all()->count();
                    else $count = $user->customer->finances()->count();

                    return $count;
                }, "info", function () use ($request) {
                    $user = $request->user();

                    if ($user->is_admin) $count = FinanceModel::all()->count();
                    else $count = $user->customer->finances()->count();

                    return $count > 0;
                }),
                MenuItem::resource(ArchivedFinance::class),
            ])->icon("currency-dollar")->canSee($can(["finance:request", "customer:finance"])),

            MenuSection::resource(ActivityLog::class)->icon('lightning-bolt')
                ->canSee(fn (Request $request) => $request->user()?->is_admin && $request->user()?->can("activity:view")),
            MenuSection::resource(CustomerApiToken::class)->icon('key')
                ->canSee(fn(Request $request) => $request->user()->is_customer)
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
