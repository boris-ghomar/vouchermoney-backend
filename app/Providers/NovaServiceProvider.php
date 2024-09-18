<?php

namespace App\Providers;

use App\Nova\Dashboards\Main as MainDashboard;
use App\Nova\Permission;
use App\Nova\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Http\Requests\NovaRequest;
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

        function can(...$permissions): \Closure
        {
            return fn(NovaRequest $request) => $request->user()->can(...$permissions);
        }

        Nova::mainMenu(fn(Request $request) => [
            MenuSection::dashboard(MainDashboard::class)->icon("view-grid"),

            MenuSection::make("Permissions", [
                MenuItem::resource(Permission::class)->canSee(can("permission.view-any")),
                MenuItem::resource(Role::class)->canSee(can("role.view-any"))
            ])->icon("key")->collapsable()->collapsedByDefault()->canSee(can("permission.view-any", "role.view-any"))
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
            new MainDashboard,
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
