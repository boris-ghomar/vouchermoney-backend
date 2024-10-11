<?php

namespace App\Providers;

use App\Models\CustomerApiToken;
use App\Models\Transaction\ArchivedTransaction;
use App\Auth\CustomerApiGuard;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Policies\ActivityPolicy;
use App\Policies\ArchivedTransactionPolicy;
use App\Policies\ArchivedVoucherPolicy;
use App\Policies\CustomerApiTokenPolicy;
use App\Policies\VoucherPolicy;
use App\Services\Activity\ActivityService;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Customer\CustomerService;
use App\Services\Finance\Contracts\FinanceServiceContract;
use App\Services\Finance\FinanceService;
use App\Services\Notification\Contracts\NotificationServiceContract;
use App\Services\Notification\NotificationService;
use App\Services\Transaction\Contracts\TransactionServiceContract;
use App\Services\Transaction\TransactionService;
use App\Services\User\Contracts\UserServiceContract;
use App\Services\User\UserService;
use App\Services\Voucher\Contracts\VoucherCodeServiceContract;
use App\Services\Voucher\Contracts\VoucherServiceContract;
use App\Services\Voucher\VoucherCodeService;
use App\Services\Voucher\VoucherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app['db.schema']->morphUsingUlids();

        $this->app->bind(VoucherCodeServiceContract::class, VoucherCodeService::class);
        $this->app->bind(VoucherServiceContract::class, VoucherService::class);
        $this->app->bind(CustomerServiceContract::class, CustomerService::class);
        $this->app->bind(UserServiceContract::class, UserService::class);
        $this->app->bind(FinanceServiceContract::class, FinanceService::class);
        $this->app->bind(NotificationServiceContract::class, NotificationService::class);
        $this->app->bind(TransactionServiceContract::class, TransactionService::class);
        $this->app->bind(ActivityServiceContract::class, ActivityService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Voucher::class, VoucherPolicy::class);
        Gate::policy(ArchivedVoucher::class, ArchivedVoucherPolicy::class);
        Gate::policy(ArchivedTransaction::class, ArchivedTransactionPolicy::class);
        Gate::policy(CustomerApiToken::class, CustomerApiTokenPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);

        Auth::extend('token', function ($app, $name, array $config) {
            return new CustomerApiGuard($app['auth']->createUserProvider($config['provider']), $app['request']);
        });
    }
}
