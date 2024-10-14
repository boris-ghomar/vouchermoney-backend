<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Notification;
use Laravel\Nova\Notifications\NovaNotification as ParentNovaNotification;

class NovaNotification
{
    const TYPE_INFO = "info";
    const TYPE_ERROR = "error";
    const TYPE_SUCCESS = "success";
    const TYPE_WARNING = "warning";

    public function __construct(
        protected ParentNovaNotification $notification
    ) {}

    public function action(string $url): static
    {
        $this->notification->action(__("View"), $url);

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->notification->icon($icon);

        return $this;
    }

    public function send($notifiable): void
    {
        Notification::send($notifiable, $this->notification);
    }

    public static function info(string $message): static
    {
        return static::create($message, static::TYPE_INFO);
    }

    public static function warning(string $message): static
    {
        return static::create($message, static::TYPE_WARNING);
    }

    public static function error(string $message): static
    {
        return static::create($message, static::TYPE_ERROR);
    }

    public static function success(string $message): static
    {
        return static::create($message, static::TYPE_SUCCESS);
    }

    public static function make(string $message): static
    {
        return static::make($message);
    }

    private static function create(string $message, string $type = self::TYPE_INFO, string $icon = ""): static
    {
        $notification = new ParentNovaNotification();
        $notification->message($message);
        $notification->type($type);

        if (! empty($icon)) $notification->icon($icon);

        return new static($notification);
    }
}
