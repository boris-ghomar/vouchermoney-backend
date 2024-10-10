<?php

namespace App\Nova;

use App\Models\Permission as Model;
use App\Nova\Fields\BelongsToMany;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use App\Nova\Resources\User\User;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class Permission extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    public static $searchable = false;
    public static $globallySearchable = false;

    public function title(): string
    {
        return $this->name_label . " - " . $this->description;
    }

    public function subtitle(): string
    {
        return $this->description_long;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__("fields.id"), "id")->onlyForAdmins()->onlyOnDetail(),

            Text::make(__("fields.name"), "name")->exceptOnForms()->onlyForAdmins(),
            Text::make(__("fields.name"), "name_label")->exceptOnForms()->onlyForCustomers(),
            Text::make(__("fields.title"), "name_title")->exceptOnForms(),
            Text::make(__("fields.description"), "description")->exceptOnForms(),
            Text::make(__("fields.description_long"), "description_long")->onlyOnDetail(),

            BelongsToMany::make(__("fields.users"), "users", User::class)
                ->onlyOnDetail()->onlyForAdmins([Model::CUSTOMERS_VIEW]),

            DateTime::createdAt()->onlyForAdmins()->onlyOnDetail(),
            DateTime::updatedAt()->onlyForAdmins(),
        ];
    }

    public function authorizedToUpdate(Request $request): false
    {
        return false;
    }
}
