<?php

namespace App\Models\Customer;

use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Services\Notification\NotificationService;

trait HasNotifications
{
    public function sendFinanceRequestedNotification(Finance $finance): void
    {
        NotificationService::sendFinanceRequestedNotification($this, $finance);
    }

    public function sendFinanceResolvedNotification(ArchivedFinance $finance): void
    {
        NotificationService::sendFinanceResolvedNotification($this, $finance);
    }
}
