<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;

use Modules\FocusCmsCoreShortcodes\Classes\Support\ShortcodeHelper;

class HtmlShortcode implements DynamicShortcodeInterface
{
    /**
     * pattern lista (nyitó és záró tag külön)
     */
    public function pattern(): string
    {
        return '/\{(div|a|p|span|i)(?:\s+([^}]+))?\}|\{e_(div|a|p|span|i)\}/';
    }


    /**
     * render
     */
    public function render(array $matches): string
    {
        /*
         * záró tag
         */
        if (!empty($matches[3])) {

            return '</'.$matches[3].'>';

        }

        /*
         * nyitó tag
         */
        $tagName =
            $matches[1];

        $attributesString =
            $matches[2] ?? '';

        $attributes = [];

        if (!empty($attributesString)) {

            /*
             * ID
             */
            if (preg_match('/#([\w\-\[\]]+)/', $attributesString, $idMatch)) {

                $attributes['id'] =
                    $idMatch[1];

            }

            /*
             * class
             */
            if (preg_match_all('/\.([\w\-\[\]]+)/', $attributesString, $classMatches)) {

                $attributes['class'] =
                    implode(' ', $classMatches[1]);

            }

            /*
             * egyéb attribútum
             */
            if (preg_match_all('/@([\w\-]+)\(([^)]*)\)/', $attributesString, $attrMatches, PREG_SET_ORDER)) {

                foreach ($attrMatches as $match) {

                    $attributes[$match[1]] =
                        $match[2] ?: $match[1];

                }
            }
        }

        return '<'
            .$tagName
            .ShortcodeHelper::buildAttributes($attributes)
            .'>';
    }
}