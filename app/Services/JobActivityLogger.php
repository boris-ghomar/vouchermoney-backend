<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class JobActivityLogger
{
    private string $group;
    private Model|null $performedOn = null;
    private User|null $causer = null;
    private string|null $status = "success";

    private string $message = "";

    public function __construct($group = "")
    {
        $this->group = $group;
    }

    public function withFailedStatus(): static
    {
        $this->status = "failed";

        return $this;
    }

    public function withSuccessStatus(): static
    {
        $this->status = "success";

        return $this;
    }

    public function withMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function performedOn(Model $model): static
    {
        $this->performedOn = $model;

        return $this;
    }

    public function causedBy(User $user): static
    {
        $this->causer = $user;

        return $this;
    }

    public function log(): void
    {
        $activity = activity()->withProperties([
            "status" => $this->status,
            "message" => $this->message,
        ]);

        if ($this->performedOn)
            $activity->performedOn($this->performedOn);

        if ($this->causer)
            $activity->causedBy($this->causer);

        $activity->log("JobActivityLogger" . ($this->group ? "::" . $this->group : ""));
    }

    public static function make($group = ""): static
    {
        return new static($group);
    }
}
