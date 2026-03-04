<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Support;

class ShortcodeHelper
{
    public static function buildAttributes(array $attributes): string
    {
        $result = '';

        foreach ($attributes as $name => $value) {

            if (is_numeric($name)) {

                $result .= ' ' . htmlspecialchars($value);

            } else {

                $result .= ' '
                    . htmlspecialchars($name)
                    . '="'
                    . htmlspecialchars($value)
                    . '"';

            }
        }

        return $result;
    }


    public static function parseLinkParams(string $paramString): array
    {
        $params = [];

        if (preg_match_all('/\.([\w-]+)/', $paramString, $matches)) {

            $params['link_classes'] = $matches[1];

        }

        if (preg_match('/#([\w-]+)/', $paramString, $matches)) {

            $params['link_id'] = $matches[1];

        }

        if (preg_match_all('/@([\w-]+)(?:\(([^)]*)\))?/', $paramString, $matches, PREG_SET_ORDER)) {

            foreach ($matches as $match) {

                $params['link_attrs'][$match[1]] =
                    $match[2] ?? $match[1];

            }
        }

        return $params;
    }
}