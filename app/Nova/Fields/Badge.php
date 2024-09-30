<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Badge as NovaBadge;

class Badge extends NovaBadge
{
    use FieldMacro;

    public function asBoolean(): static
    {
        $this->map([
            0 => "danger",
            1 => "success"
        ]);

        return $this;
    }
}
