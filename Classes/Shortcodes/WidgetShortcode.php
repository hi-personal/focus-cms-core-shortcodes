<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;
use App\Models\Post;
use Modules\FocusCmsCoreShortcodes\Classes\Support\ShortcodeHelper;

class WidgetShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\[widget\s+([^\]]+)\]/s';
    }


    public function render(array $matches): string
    {
        $params = $this->parseWidgetParams($matches[1]);

        if (empty($params['id'])) {
            return '';
        }

        $widget = Post::find($params['id']);

        if (!$widget || $widget->status !== 'published') {
            return '';
        }


        $title = $this->resolveWidgetValue($params['title'] ?? '', $widget);
        $type = $params['type'] ?? null;

        $content = markdownToHtml(
            $this->resolveWidgetValue($params['content'] ?? '', $widget),
            false
        );


        // csak content mód
        if ($type === 'only-content') {
            return $content;
        }


        // külső attribútumok
        $widgetAttrs = [
            'class' => 'sidebar-widget '.($params['classes'] ?? '')
        ];

        if (!empty($params['id'])) {
            $widgetAttrs['id'] = 'widget-'.$params['id'];
        }


        // head attribútumok
        $headAttrs = [
            'class' => 'sidebar-widget-head '.($params['head_classes'] ?? '')
        ];

        if (!empty($params['head_id'])) {
            $headAttrs['id'] = $params['head_id'];
        }


        // content attribútumok
        $contentAttrs = [
            'class' => 'sidebar-widget-content '.($params['content_classes'] ?? '')
        ];

        if (!empty($params['content_id'])) {
            $contentAttrs['id'] = $params['content_id'];
        }


        // HTML
        $html = '<div'.ShortcodeHelper::buildAttributes($widgetAttrs).'>';

        if (!empty($title)) {
            $html .= '<div'.ShortcodeHelper::buildAttributes($headAttrs).'>'
                .$title
                .'</div>';
        }

        $html .= '<div'.ShortcodeHelper::buildAttributes($contentAttrs).'>'
            .$content
            .'</div>';

        $html .= '</div>';

        return $html;
    }



    protected function parseWidgetParams(string $paramString): array
    {
        $params = [];


        if (preg_match('/id\((\d+)\)/', $paramString, $m)) {
            $params['id'] = (int)$m[1];
        }

        if (preg_match('/title\(([^)]+)\)/', $paramString, $m)) {
            $params['title'] = $m[1];
        }

        if (preg_match('/type\(([^)]+)\)/', $paramString, $m)) {
            $params['type'] = $m[1];
        }

        if (preg_match('/content\(([^)]+)\)/', $paramString, $m)) {
            $params['content'] = $m[1];
        }


        // container class
        if (preg_match_all('/\.([\w\-]+)/', $paramString, $m)) {
            $params['classes'] = implode(' ', $m[1]);
        }


        // head blokk
        if (preg_match('/head\{([^}]*)\}/', $paramString, $headMatch)) {

            if (preg_match('/#([\w\-]+)/', $headMatch[1], $m)) {
                $params['head_id'] = $m[1];
            }

            if (preg_match_all('/\.([\w\-]+)/', $headMatch[1], $m)) {
                $params['head_classes'] = implode(' ', $m[1]);
            }
        }


        // content blokk
        if (preg_match('/content\{([^}]*)\}/', $paramString, $contentMatch)) {

            if (preg_match('/#([\w\-]+)/', $contentMatch[1], $m)) {
                $params['content_id'] = $m[1];
            }

            if (preg_match_all('/\.([\w\-]+)/', $contentMatch[1], $m)) {
                $params['content_classes'] = implode(' ', $m[1]);
            }
        }


        return $params;
    }



    protected function resolveWidgetValue(string $value, Post $widget): string
    {
        if ($value === '@title') {
            return $widget->title;
        }

        if ($value === '@content') {
            return $widget->content ?? '';
        }

        return $value;
    }
}