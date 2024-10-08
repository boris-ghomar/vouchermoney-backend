<?php

namespace App\Nova\Filters;

use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaInputFilter\InputFilter;

class AmountFilter extends InputFilter
{
    protected string $type;
    protected string $column = "amount";
    public $inputType = "number";

    public function forColumns($columns): static
    {
        $this->column = $columns;

        return $this;
    }

    public function min(): static
    {
        $this->type = "min";

        return $this;
    }

    public function max(): static
    {
        $this->type = "max";

        return $this;
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return $this->type === 'min' ? 'min_amount_filter' : 'max_amount_filter';
    }

    /**
     * @return string
     */

    public function name(): string
    {
        return ucfirst($this->type) . " " . ucfirst($this->column);
    }

    /**
     * @param NovaRequest $request
     * @param $query
     * @param $search
     * @return void
     */
    public function apply(NovaRequest $request, $query, $search): void
    {
        $query->whereRaw("ABS(" . $this->column . ") " . ($this->type === 'max' ? "<" : ">") . "= ?", [$search]);
    }

    public static function make(...$arguments): array
    {
        $min = parent::make()->min();
        $max = parent::make()->max();
        return [$min, $max];
    }
}
