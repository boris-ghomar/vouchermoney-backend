<?php

namespace App\Providers;

use App\Nova\ActiveVoucher;
use App\Nova\Dashboards\CustomerBalance;
use App\Nova\Lenses\ResolvedVouchers;
use App\Nova\Lenses\UsedVouchers;
use App\Nova\Voucher;
use App\Nova\Customer;
use App\Nova\Dashboards\Main as MainDashboard;
use App\Nova\Finance;
use App\Nova\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
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

        Nova::initialPath("/");

        $can = fn(...$permissions) => fn(NovaRequest $request) => $request->user()?->canAny(...$permissions);

        Nova::userMenu(function (Request $request, Menu $menu) {
            $menu->prepend(
                MenuItem::make(
                    'Profile',
                    "/resources/users/{$request->user()?->getKey()}"
                ),
            );

            return $menu;
        });

        Nova::mainMenu(fn(Request $request) => [
            MenuSection::dashboard(CustomerBalance::class)->icon("credit-card")
                ->canSee(fn(Request $request) => $request->user()?->is_customer),

            MenuSection::resource(User::class)->icon("users"),
            MenuSection::resource(Customer::class)->icon("user-group")
                ->canSee($can(["customer:view-any"])),

            MenuSection::make("Vouchers", [
                MenuItem::resource(ActiveVoucher::class),
                MenuItem::lens(ActiveVoucher::class, ResolvedVouchers::class),
                MenuItem::lens(ActiveVoucher::class, UsedVouchers::class),
            ])->icon("cash")->canSee(fn(Request $request) => $request->user()?->is_customer),

            MenuSection::resource(Finance::class)->icon("currency-dollar")
                ->canSee($can(["finance:request", "customer:finance"])),
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
            new CustomerBalance(),
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools(): array
    {
        return [

        ];
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
