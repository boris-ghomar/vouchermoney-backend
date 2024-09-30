<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Currency as NovaCurrency;

class Currency extends NovaCurrency
{
    use FieldMacro;

    public function displayAsPositive(): static
    {
        return $this->displayUsing(fn ($amount) => $this->formatMoney(abs($amount)));
    }
}
