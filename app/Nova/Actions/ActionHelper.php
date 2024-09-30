<?php

namespace App\Nova\Actions;
use Laravel\Nova\Actions\Action;

class ActionHelper
{
    /**
     * @param array<Action | array<Action>> $actions
     * @return array
     */
    public static function make(array $actions): array
    {
        $result = [];

        foreach ($actions as $action) {
            if (is_array($action)) {
                foreach ($action as $item) $result[] = $item;
                continue;
            }

            $result[] = $action;
        }

        return $result;
    }
}
