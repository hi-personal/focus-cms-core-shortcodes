<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;

class DateShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\{date(?:\s+([^}]+))?\}/';
    }

    public function render(array $matches): string
    {
        $param = trim($matches[1] ?? '');

        /*
         * default format
         */
        $format = 'Y';

        /*
         * format="..."
         */
        if (preg_match('/format="([^"]+)"/', $param, $m)) {

            $format = $m[1];

        }
        /*
         * shorthand {date Y-m-d}
         */
        elseif (!empty($param)) {

            $format = $param;

        }

        /*
         * biztonsági whitelist
         * csak engedélyezett karakterek
         */
        if (!preg_match('/^[dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU\-:\.\/\s]+$/', $format)) {

            return '';

        }

        return date($format);
    }
}