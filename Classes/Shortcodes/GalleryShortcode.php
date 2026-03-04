<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;
use App\Models\PostImage;
use Modules\FocusCmsCoreShortcodes\Classes\Support\ShortcodeHelper;

class GalleryShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\[gallery\s+([^\]]+)\]/';
    }


    public function render(array $matches): string
    {
        $params = $this->parseGalleryParams($matches[1]);

        if (empty($params['ids'])) {
            return '';
        }

        $images = PostImage::whereIn('id', $params['ids'])
            ->orderByRaw('FIELD(id,'.implode(',', $params['ids']).')')
            ->get()
            ->keyBy('id');

        if ($images->isEmpty()) {
            return '';
        }

        return '<!-- GALLERY -->'."\n\n"
            .$this->generateGalleryHtml($images, $params['ids'], $params);
    }


    protected function parseGalleryParams(string $paramString): array
    {
        $params = [
            'size' => 'thumbnail',
            'link_size' => 'original',
            'attrs' => []
        ];

        if (preg_match('/ids\(([^)]+)\)/', $paramString, $m)) {
            $params['ids'] = array_map('intval', explode(',', $m[1]));
        }

        if (preg_match('/alt\(([^)]+)\)/', $paramString, $m)) {
            $params['alt'] = $m[1];
        }

        if (preg_match('/size\(([\w-]+)\)/', $paramString, $m)) {
            $params['size'] = $m[1];
        }

        if (preg_match('/link-size\(([\w-]+)\)/', $paramString, $m)) {
            $params['link_size'] = $m[1];
        }

        if (preg_match('/mode\(([^)]+)\)/', $paramString, $m)) {
            $params['mode'] = $m[1];
        }

        if (preg_match_all('/@([\w-]+)\(([^)]*)\)/', $paramString, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $params['attrs'][$match[1]] = $match[2] ?: $match[1];
            }
        }

        return $params;
    }


    protected function generateGalleryHtml($images, array $ids, array $params): string
    {
        $galleryAttrs = [
            'class' => 'image-gallery image-gallery-wrapper my-6 relative block',
            'data-pswp' => ''
        ];

        foreach ($params['attrs'] as $attr => $value) {
            $galleryAttrs[$attr] = $value;
        }

        $html = '<div'.ShortcodeHelper::buildAttributes($galleryAttrs).'>';


        if (($params['mode'] ?? null) === 'list') {

            foreach ($ids as $id) {

                if (!isset($images[$id])) {
                    continue;
                }

                $image = $images[$id];

                $description = $image->meta('description');

                $thumbUrl = $image->getImageUrl($params['size']);
                $fullUrl = $image->getImageUrl($params['link_size']);

                $altText = $params['alt'] ?? $image->title;

                $html .= '<div class="image-gallery image-with-viewer-with-text" data-pswp="">';

                $html .= '<figure class="img-figure'
                    .($description ? '-with-text' : '')
                    .'" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';

                $html .= '<a class="img-figure-a"'
                    .' href="'.htmlspecialchars($fullUrl).'"'
                    .' itemprop="contentUrl"'
                    .' data-pswp-width="'.$image->width.'"'
                    .' data-pswp-height="'.$image->height.'">';

                $html .= '<img class="img-figure-img"'
                    .' src="'.htmlspecialchars($thumbUrl).'"'
                    .' itemprop="thumbnail"'
                    .' alt="'.htmlspecialchars($altText).'">';

                $html .= '</a>';

                $html .= '<figcaption class="img-figcaption" itemprop="caption description">'
                    .markdownToHtml($description)
                    .'</figcaption>';

                $html .= '</figure></div>';
            }
        }
        else {

            $html .= '<div class="gallery-container">';

            foreach ($ids as $id) {

                if (!isset($images[$id])) {
                    continue;
                }

                $image = $images[$id];

                $thumbUrl = $image->getImageUrl($params['size']);
                $fullUrl = $image->getImageUrl($params['link_size']);

                $altText = $params['alt'] ?? $image->title;

                $html .= '<figure class="gallery-item"'
                    .' itemprop="associatedMedia"'
                    .' itemscope itemtype="http://schema.org/ImageObject">';

                $html .= '<a class="gallery-item-link"'
                    .' href="'.htmlspecialchars($fullUrl).'"'
                    .' itemprop="contentUrl"'
                    .' data-pswp-width="'.$image->width.'"'
                    .' data-pswp-height="'.$image->height.'">';

                $html .= '<img class="gallery-item-link-image"'
                    .' src="'.htmlspecialchars($thumbUrl).'"'
                    .' itemprop="thumbnail"'
                    .' alt="'.htmlspecialchars($altText).'" />';

                $html .= '</a>';

                $html .= '</figure>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}