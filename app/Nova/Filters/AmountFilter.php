<?php

namespace App\Nova\Filters;

use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaInputFilter\InputFilter;

class AmountFilter extends InputFilter
{
    protected string $filterType;
    protected string $column = "amount";
    public $inputType = "number";

    public function forColumns($columns): static
    {
        $this->column = $columns;

        return $this;
    }

    /**
     * @param string $filterType
     * @return $this
     */
    public function withFilterType(string $filterType): self
    {
        $this->filterType = $filterType;
        return $this;
    }
    /**
     * @return string
     */
    public function key(): string
    {
        return $this->filterType === 'min' ? 'min_amount_filter' : 'max_amount_filter';
    }

    /**
     * @return string
     */

    public function name(): string
    {
        return $this->filterType === 'min' ? 'Minimum Amount' : 'Maximum Amount';
    }

    /**
     * @param NovaRequest $request
     * @param $query
     * @param $search
     * @return void
     */
    public function apply(NovaRequest $request, $query, $search): void
    {
        $query->whereRaw("ABS(" . $this->column . ") " . ($this->filterType === 'max' ? "<" : ">") . "= ?", [$search]);
    }

    public static function make(...$arguments): array
    {
        return [
            parent::make()->withFilterType('min'),
            parent::make()->withFilterType('max'),
        ];
    }
}
