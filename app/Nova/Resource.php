<?php

namespace App\Nova;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource as NovaResource;
use Illuminate\Database\Eloquent\Builder;

abstract class Resource extends NovaResource
{
    protected static string $default_order_column;
    protected static string $default_order_dir;

    /**
     * Build an "index" query for the given resource.
     *
     * @param  NovaRequest  $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        if (! empty(static::$default_order_column)) {
            $query->when(empty($request->get('orderBy')), function(Builder $q) {
                $q->getQuery()->orders = [];

                return $q->orderBy(static::$default_order_column, static::$default_order_dir ?? "desc");
            });
        }

        return $query;
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param  NovaRequest  $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function detailQuery(NovaRequest $request, $query): Builder
    {
        return parent::detailQuery($request, $query);
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param  NovaRequest  $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        return parent::relatableQuery($request, $query);
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }

    public static function forCustomer(NovaRequest $request, Builder $query, string $column = "customer_id"): void
    {
        /** @var User $user */
        $user = $request->user();

        if ($user && $user->is_customer)
            $query->where($column, $user->customer_id);
    }

    public static function hideQuery(Builder $query): void
    {
        $query->whereRaw("1 = 0");
    }

    public static function hideWhenNotAuthorized(NovaRequest $request, Builder $query, iterable|string $abilities): void
    {
        /** @var User $user */
        $user = $request->user();

        if ($user && $user->canAny($abilities)) return;

        static::hideQuery($query);
    }
}
