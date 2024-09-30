<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;

class FieldHelper
{
    public static string $dateTimeFormat = 'D d/m/Y, g:ia';

    public static function makeDatetimeField(string $title, string $attribute): DateTime
    {
        return DateTime::make($title, $attribute)
            ->displayUsing(fn ($value) => $value?->format(static::$dateTimeFormat) ?: "");
    }

    /**
     * @param array<Field | array<Field>> $fields
     * @return array
     */
    public static function make(array $fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            if (is_array($field)) {
                foreach ($field as $item) $result[] = $item;
                continue;
            }

            $result[] = $field;
        }

        return $result;
    }
}
