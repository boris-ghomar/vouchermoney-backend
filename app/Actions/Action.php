<?php

namespace App\Actions;

use App\Exceptions\NotAuthorized;
use Illuminate\Http\Request;

abstract class Action
{
    final public static function run(Action $action): void
    {
        $action->execute();
    }

    /**
     * Determines if user can run this action.
     *
     * @param Request $request
     * @return bool
     */
    public function authorizedToRun(Request $request): bool
    {
        return true;
    }

    /**
     * @throws NotAuthorized
     */
    final function execute(Request $request): void
    {
        if (! $this->authorizedToRun($request->user())) {
            throw new NotAuthorized();
        }
    }
}
