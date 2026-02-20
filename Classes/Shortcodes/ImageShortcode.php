<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;
use App\Models\PostImage;
use App\Models\PostImageSize;
use Modules\FocusCmsCoreShortcodes\Classes\Support\ShortcodeHelper;

class ImageShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\[image\s+([^\]]+)\]/';
    }


    public function render(array $matches): string
    {
        $params = $this->parseImageParams($matches[1]);

        if (empty($params['id'])) {
            return '';
        }

        $image = PostImage::find($params['id']);

        if (!$image) {
            return '';
        }

        $postImageSize = PostImageSize::where('post_image_id', $image->id)
            ->where('name', $params['size'] ?? 'thumbnail')
            ->first();


        // TEXT feloldás
        $params['text'] = match($params['text'] ?? null) {
            '@title' => $image->title,
            '@description' => $image->meta('description'),
            default => $params['text'] ?? null
        };


        // ALT kezelés
        $altText = '';

        if (isset($params['alt'])) {

            if ($params['alt'] === true) {
                $altText = $image->meta('alt_text') ?? $image->title;
            }
            elseif (is_string($params['alt'])) {
                $altText = match($params['alt']) {
                    '@title' => $image->title,
                    '@name' => $image->name,
                    default => $params['alt']
                };
            }
        }


        // IMG attribútumok
        $imgAttrs = [
            'src' => $image->getImageUrl($params['size'] ?? 'thumbnail'),
            'class' => implode(' ', $params['img_classes'] ?? [])
        ];

        if (!empty($altText)) {
            $imgAttrs['alt'] = $altText;
        }

        if (!empty($params['img_id'])) {
            $imgAttrs['id'] = $params['img_id'];
        }

        foreach ($params['img_attrs'] ?? [] as $k => $v) {
            $imgAttrs[$k] = $v;
        }


        // nincs link
        if (empty($params['link']) && empty($params['link-ext'])) {

            return '<img'
                .ShortcodeHelper::buildAttributes($imgAttrs)
                .' width="'.$postImageSize?->width.'"'
                .' height="'.$postImageSize?->height.'"'
                .'>';
        }


        // LINK attribútumok
        $linkAttrs = [
            'target' => $params['link_target'] ?? '_blank'
        ];


        if (isset($params['link'])) {

            if (str_starts_with($params['link'], '@')) {
                $size = substr($params['link'], 1);
                $linkAttrs['href'] = $image->getImageUrl($size);
            }
            else {
                $linkAttrs['href'] = $params['link'];
            }
        }


        // DOWNLOAD
        if (isset($params['download'])) {

            $extension = $image->file_extension ? '.'.$image->file_extension : '';

            if ($params['download'] === '@title') {
                $linkAttrs['download'] = $image->title.$extension;
            }
            elseif ($params['download'] === '@name') {
                $linkAttrs['download'] = $image->name.$extension;
            }
            else {
                $linkAttrs['download'] = $params['download'].$extension;
            }
        }


        // LINK class / id / attrs
        if (!empty($params['link_classes'])) {
            $linkAttrs['class'] = implode(' ', $params['link_classes']);
        }

        if (!empty($params['link_id'])) {
            $linkAttrs['id'] = $params['link_id'];
        }

        foreach ($params['link_attrs'] ?? [] as $k => $v) {
            $linkAttrs[$k] = $v;
        }


        // VIEWER mód
        if (($params['mode'] ?? null) === 'viewer') {

            $html = '<div class="image-gallery image-with-viewer'
                .(!empty($params['text']) ? '-with-text ' : ' ')
                .$imgAttrs['class'].'" data-pswp="">';

            $html .= '<figure class="img-figure'
                .(!empty($params['text']) ? '-with-text' : '')
                .'" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';

            $html .= '<a'
                .ShortcodeHelper::buildAttributes($linkAttrs)
                .' itemprop="contentUrl"'
                .' data-pswp-width="'.$image->width.'"'
                .' data-pswp-height="'.$image->height.'">';

            $html .= '<img'
                .ShortcodeHelper::buildAttributes($imgAttrs)
                .' width="'.$postImageSize?->width.'"'
                .' height="'.$postImageSize?->height.'"'
                .' itemprop="thumbnail">';

            $html .= '</a>';

            if (!empty($params['text'])) {
                $html .= '<figcaption class="img-figcaption" itemprop="caption description">'
                    .markdownToHtml($params['text'])
                    .'</figcaption>';
            }

            $html .= '</figure></div>';

            return $html;
        }


        // normál linkelt kép
        return '<a'
            .ShortcodeHelper::buildAttributes($linkAttrs)
            .'><img'
            .ShortcodeHelper::buildAttributes($imgAttrs)
            .' width="'.$postImageSize?->width.'"'
            .' height="'.$postImageSize?->height.'"'
            .'></a>';
    }



    protected function parseImageParams(string $paramString): array
    {
        $params = [];

        if (preg_match('/id\((\d+)\)/', $paramString, $m)) {
            $params['id'] = (int)$m[1];
        }

        if (preg_match('/size\(([\w-]+)\)/', $paramString, $m)) {
            $params['size'] = $m[1];
        }

        if (preg_match('/link\(([^)]+)\)/', $paramString, $m)) {
            $params['link'] = $m[1];
        }

        if (preg_match('/mode\(([^)]+)\)/', $paramString, $m)) {
            $params['mode'] = $m[1];
        }

        if (preg_match('/text\(([^)]+)\)/', $paramString, $m)) {
            $params['text'] = $m[1];
        }

        if (preg_match('/alt(?:\(([^)]*)\))?/', $paramString, $m)) {
            $params['alt'] = $m[1] ?? true;
        }

        if (preg_match('/download\(([^)]*)\)/', $paramString, $m)) {
            $params['download'] = $m[1];
        }

        if (preg_match('/link\{([^}]*)\}/', $paramString, $m)) {
            $params = array_merge($params, ShortcodeHelper::parseLinkParams($m[1]));
        }

        if (preg_match_all('/\.([\w-]+)(?![^{]*\})/', $paramString, $m)) {
            $params['img_classes'] = $m[1];
        }

        if (preg_match('/#([\w-]+)(?![^{]*\})/', $paramString, $m)) {
            $params['img_id'] = $m[1];
        }

        if (preg_match_all('/@([\w-]+)\(([^)]*)\)(?![^{]*\})/', $paramString, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $params['img_attrs'][$match[1]] = $match[2] ?: $match[1];
            }
        }

        return $params;
    }
}