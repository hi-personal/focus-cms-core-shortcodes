<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;

class UrlShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\{url(?:\s+([^}]+))?\}/';
    }

    public function render(array $matches): string
    {
        $text = trim($matches[1] ?? '');

        if ($text === '') {
            return '';
        }

        /*
         * route="..."
         */
        if (preg_match('/route="([^"]+)"/', $text, $m)) {

            $route = $m[1];

            $params = [];

            preg_match_all(
                '/([\w\-]+)="([^"]*)"/',
                $text,
                $paramMatches,
                PREG_SET_ORDER
            );

            foreach ($paramMatches as $match) {

                if ($match[1] !== 'route') {

                    $params[$match[1]] = $match[2];

                }
            }

            try {

                return route($route, $params);

            } catch (\Throwable $e) {

                return '';
            }
        }

        /*
         * path="..."
         */
        if (preg_match('/path="([^"]+)"/', $text, $m)) {

            return url($m[1]);

        }

        /*
         * shorthand first token only
         * removes .class, @attr, #id
         */
        if (preg_match('/^([^\s\.\@\#]+)/', $text, $m)) {

            return url($m[1]);

        }

        return '';
    }
}