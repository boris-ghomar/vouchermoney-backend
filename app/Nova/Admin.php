<?php

namespace App\Nova;

class Admin extends User
{
    /**
     * @return string
     */
    public static function label(): string
    {
        return "Admins";
    }
}
