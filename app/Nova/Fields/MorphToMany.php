<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\MorphToMany as NovaMorphToMany;

class MorphToMany extends NovaMorphToMany
{
    use FieldMacro;
}
