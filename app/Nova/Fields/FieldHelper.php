<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Field;

class FieldHelper
{
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
