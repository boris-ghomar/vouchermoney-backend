<?php

namespace App\Services\Activity\Contracts;

use Exception;
use Spatie\Activitylog\Contracts\Activity;

interface ActivityServiceContract
{
    public function novaException(Exception $exception, array $properties = []): Activity|null;
    public function commandException(Exception $exception, array $properties = []): Activity|null;
    public function activity(string $name, string $description, array $properties = []): Activity|null;
}
