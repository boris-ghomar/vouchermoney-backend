<?php

namespace App\Services\Activity\Contracts;

use App\Models\CustomerApiTokenActivity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Contracts\Activity;

interface ActivityServiceContract
{
    public function novaException(Exception $exception, array $properties = []): Activity|null;
    public function commandException(Exception $exception, array $properties = []): Activity|null;
    public function apiException(Exception $exception, array $properties = []): Activity|null;
    public function activity(string $name, string $description, array $properties = []): Activity|null;

    public function apiActivity(string $action, Request $request, JsonResponse $response, array $properties = []): CustomerApiTokenActivity;
}
