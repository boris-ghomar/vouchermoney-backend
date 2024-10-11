<?php

namespace App\Services\Activity;

use App\Models\CustomerApiTokenActivity;
use App\Services\Activity\Contracts\ActivityServiceContract;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\Activitylog\Contracts\Activity;

class ActivityService implements ActivityServiceContract
{
    public function novaException(Exception $exception, array $properties = []): Activity|null
    {
        return $this->forNova(
            "exception",
            $exception->getMessage(),
            array_merge(["exception" => $exception], $properties)
        );
    }

    public function apiException(Exception $exception, array $properties = []): Activity|null
    {
        return $this->forApi(
            "exception",
            $exception->getMessage(),
            array_merge(["exception" => $exception], $properties)
        );
    }

    public function commandException(Exception $exception, array $properties = []): Activity|null
    {
        return $this->forCommand(
            "exception",
            $exception->getMessage(),
            array_merge(["exception" => $exception], $properties));
    }

    protected function forCommand(string $name, string $description, array $properties = []): Activity|null
    {
        return $this->make("command:$name", $description, $properties);
    }

    protected function forApi(string $name, string $description, array $properties = []): Activity|null
    {
        return $this->make("api:$name", $description, $properties);
    }

    public function activity(string $name, string $description, array $properties = []): Activity|null
    {
        return $this->make("activity:$name", $description, $properties);
    }

    protected function forNova(string $name, string $description, array $properties = []): Activity|null
    {
        return $this->make("nova:$name", $description, $properties);
    }

    protected function make(string $name, string $description, array $properties = []): Activity|null
    {
        $log = activity("logger:$name")->causedBy($this->getCauser());

        if (! empty($properties)) $log->withProperties($properties);

        return $log->log($description);
    }

    protected function getCauser(): ?Authenticatable
    {
        return auth()->user();
    }

    public function apiActivity(string $action, $request, $response, array $properties = []): CustomerApiTokenActivity
    {
        $activity = new CustomerApiTokenActivity();
        $activity->action = $action;
        $activity->token()->associate(auth()->user());
        $activity->request = $request;
        $activity->response = $response;

        if (! empty($properties)) $activity->properties = $properties;

        $activity->save();

        return $activity;
    }
}
