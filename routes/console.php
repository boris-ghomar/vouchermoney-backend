<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command("transaction:archive")->everyMinute()->withoutOverlapping();
Schedule::command("vouchers:expire")->daily();
