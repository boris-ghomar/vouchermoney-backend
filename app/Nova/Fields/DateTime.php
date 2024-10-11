<?php

namespace App\Nova\Fields;

use App\Models\User;
use Carbon\Carbon;
use Laravel\Nova\Fields\DateTime as NovaDateTime;

class DateTime extends NovaDateTime
{
    use FieldMacro;

    const FORMAT = 'd/m/Y, H:i';

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        /** @var User $user */
        $user = auth()->user();

        $this->displayUsing(fn ($value) => $value instanceof Carbon ? $value->timezone($user->timezone)->format(static::FORMAT) : $value);
    }

    public static function createdAt($customTitle = ""): static
    {
        return DateTime::make(!empty($customTitle) ? $customTitle : __("fields.created_at"), "created_at");
    }

    public static function updatedAt($customTitle = ""): static
    {
        return DateTime::make(!empty($customTitle) ? $customTitle : __("fields.updated_at"), "updated_at")
            ->onlyOnDetail();
    }

    public static function timestamps(): array
    {
        return [
            static::createdAt(),
            static::updatedAt()
        ];
    }
}
