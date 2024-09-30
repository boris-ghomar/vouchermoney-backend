<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\BelongsTo as NovaBelongsTo;

class BelongsTo extends NovaBelongsTo
{
    use FieldMacro;
}
