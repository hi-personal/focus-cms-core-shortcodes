<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;
use Modules\FocusCmsCoreShortcodes\Classes\Support\ShortcodeHelper;

class LinkShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\{link(?:\s+([^}]+))?\}|\{e_link\}/';
    }

    public function render(array $matches): string
    {
        /*
         * closing
         */
        if ($matches[0] === '{e_link}') {

            return '</a>';

        }

        $attributesString = $matches[1] ?? '';

        $attributes = [];

        $url = '';

        /*
         * shorthand URL (first param)
         */
        if ($attributesString && !str_contains($attributesString, '=')
            && !str_contains($attributesString, '@')
            && !str_contains($attributesString, '.')
            && !str_contains($attributesString, '#')) {

            $url = url(trim($attributesString));

        } else {

            $url = $this->extractUrl($attributesString);

            $attributes = $this->extractAttributes($attributesString);
        }

        /*
         * href kötelező
         */
        $attributes['href'] = $url;

        /*
         * target default
         */
        if (!isset($attributes['target'])) {

            $attributes['target'] = '_blank';

        }

        /*
         * rel auto security
         */
        if ($attributes['target'] === '_blank') {

            if (!isset($attributes['rel'])) {

                $attributes['rel'] = 'noopener noreferrer';

            }
        }

        return '<a'.ShortcodeHelper::buildAttributes($attributes).'>';
    }


    protected function extractUrl(string $text): string
    {
        /*
         * route="..."
         */
        if (preg_match('/route="([^"]+)"/', $text, $m)) {

            $route = $m[1];

            $params = [];

            preg_match_all(
                '/([\w\-]+)="([^"]*)"/',
                $text,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {

                if ($match[1] !== 'route') {

                    $params[$match[1]] = $match[2];

                }
            }

            return route($route, $params);
        }

        /*
         * path="..."
         */
        if (preg_match('/path="([^"]+)"/', $text, $m)) {

            return url($m[1]);
        }

        /*
         * first token fallback
         */
        if (preg_match('/^([^\s]+)/', trim($text), $m)) {

            return url($m[1]);
        }

        return '';
    }


    protected function extractAttributes(string $text): array
    {
        $attributes = [];

        /*
         * id
         */
        if (preg_match('/#([\w\-\[\]]+)/', $text, $m)) {

            $attributes['id'] = $m[1];
        }

        /*
         * class
         */
        if (preg_match_all('/@([\w\-\:\.]+)\(([^)]*)\)/', $text, $m)) {

            $attributes['class'] = implode(' ', $m[1]);
        }

        /*
         * @attr(value)
         */
        if (preg_match_all(
            '/@([\w\-]+)(?:\(([^)]*)\))?/',
            $text,
            $matches,
            PREG_SET_ORDER
        )) {

            foreach ($matches as $match) {

                $name = $match[1];

                $value = $match[2] ?: true;

                if ($name === 'target') {

                    $attributes['target'] = $value ?: '_blank';

                } else {

                    $attributes[$name] = $value;
                }
            }
        }

        return $attributes;
    }
}