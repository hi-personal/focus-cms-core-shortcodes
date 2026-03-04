<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;
use Carbon\Carbon;

class DateShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\{date(?:\s+([^}]+))?\}/';
    }

    public function render(array $matches): string
    {
        $paramString = trim($matches[1] ?? '');

        $attributes = $this->parseAttributes($paramString);

        /*
         * Alapérték: now()
         */
        $date = Carbon::now();

        /*
         * Fix dátum
         */
        if (!empty($attributes['date'])) {

            try {
                $date = Carbon::parse($attributes['date']);
            } catch (\Exception $e) {
                return '';
            }
        }

        /*
         * Diff mód
         */
        if (!empty($attributes['diff'])) {

            try {

                $from = Carbon::parse($attributes['diff']);

                return (string) $from->diffInYears(Carbon::now());

            } catch (\Exception $e) {

                return '';

            }
        }

        /*
         * Formátum
         */
        $format = $attributes['format'] ?? 'Y';

        /*
         * Biztonsági whitelist
         */
        if (!preg_match('/^[dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU\-:\.\/\s]+$/', $format)) {
            return '';
        }

        return $date->format($format);
    }


    protected function parseAttributes(string $text): array
    {
        $attributes = [];

        if (preg_match_all(
            '/([\w\-]+)="([^"]*)"/',
            $text,
            $matches,
            PREG_SET_ORDER
        )) {

            foreach ($matches as $match) {

                $attributes[$match[1]] = $match[2];

            }
        }

        /*
         * shorthand: {date Y-m-d}
         */
        if (empty($attributes) && !empty($text)) {
            $attributes['format'] = trim($text);
        }

        return $attributes;
    }
}