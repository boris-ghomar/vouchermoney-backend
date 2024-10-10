<?php

namespace App\Nova\Fields;

use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Fields\Text as NovaText;
use Closure;

class Text extends NovaText
{
    use FieldMacro;

    /**
     * @throws HelperNotSupported
     */
    public static function link(string $name, string|Closure $href, string|Closure $title): Text
    {
        return static::make($name, function () use ($href, $title) {
            $href = is_callable($href) ? $href() : $href;
            $title = is_callable($title) ? $title() : $title;

            if (empty($href) || empty($title)) {
                return null;
            }

            return "<a style='color: #0EA5E9; font-weight: bold' href='$href'>$title</a>";
        })->asHtml()->onlyOnDetail();
    }
}
