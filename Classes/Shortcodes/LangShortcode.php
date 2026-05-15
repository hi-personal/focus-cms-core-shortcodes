<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;

class LangShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\{current_lang(\s+[^}]*)?\}/';
    }

    public function render(array $matches): string
    {
        $paramString = trim($matches[1] ?? '');

        $attributes = $this->parseAttributes($paramString);

        $locale = (string) cms_locale();

        switch ($attributes['case'] ?? null) {

            case 'upper':
                return mb_strtoupper($locale);

            case 'lower':
                return mb_strtolower($locale);

            case 'ucfirst':
                return mb_strtoupper(mb_substr($locale, 0, 1))
                    . mb_substr($locale, 1);

            case 'locale':
                return str_replace('-', '_', locale_get_default());

            default:
                return $locale;
        }
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

        return $attributes;
    }
}