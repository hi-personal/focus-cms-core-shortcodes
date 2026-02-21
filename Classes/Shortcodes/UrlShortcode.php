<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;

class UrlShortcode implements DynamicShortcodeInterface
{
    /**
     * pattern
     *
     * {url path}
     * {url path="..."}
     * {url route="name" slug="value"}
     */
    public function pattern(): string
    {
        return '/\{url(?:\s+([^}]+))?\}/';
    }

    /**
     * render
     */
    public function render(array $matches): string
    {
        $attributesString = $matches[1] ?? '';

        if (empty($attributesString)) {
            return '';
        }

        /*
         * attribútumok parse
         */
        $attributes = $this->parseAttributes($attributesString);

        /*
         * route alapú
         */
        if (isset($attributes['route'])) {

            $routeName = $attributes['route'];

            unset($attributes['route']);

            try {

                return route($routeName, $attributes);

            } catch (\Throwable $e) {

                return '';

            }
        }

        /*
         * path attribútum
         */
        if (isset($attributes['path'])) {

            return url($attributes['path']);

        }

        /*
         * shorthand: {url categories/test}
         */
        return url(trim($attributesString));
    }

    /**
     * attribútum parser
     */
    protected function parseAttributes(string $text): array
    {
        $attributes = [];

        preg_match_all(
            '/([\w\-]+)="([^"]*)"/',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {

            $attributes[$match[1]] = $match[2];

        }

        return $attributes;
    }
}