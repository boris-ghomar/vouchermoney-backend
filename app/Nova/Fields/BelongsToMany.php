<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\BelongsToMany as NovaBelongsToMany;

class BelongsToMany extends NovaBelongsToMany
{
    use FieldMacro;
}
