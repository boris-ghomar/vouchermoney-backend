<?php

namespace App\Providers;

use App\Auth\CustomerApiGuard;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Models\Voucher\VoucherActivity;
use App\Policies\ArchivedVoucherPolicy;
use App\Policies\VoucherActivityPolicy;
use App\Policies\VoucherPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app['db.schema']->morphUsingUlids();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Voucher::class, VoucherPolicy::class);
        Gate::policy(ArchivedVoucher::class, ArchivedVoucherPolicy::class);
        Gate::policy(VoucherActivity::class, VoucherActivityPolicy::class);
        Auth::extend('customer-api', function ($app, $name, array $config) {
            return new CustomerApiGuard($app['auth']->createUserProvider($config['provider']), $app['request']);
        });
    }
}
