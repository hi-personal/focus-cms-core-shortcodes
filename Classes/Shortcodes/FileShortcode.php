<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;
use App\Models\PostFile;
use App\Models\PostImage;

class FileShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/\[(file|image_file)\s+([^\]]+)\]/';
    }


    public function render(array $matches): string
    {
        $type = $matches[1] === 'image_file' ? 'image' : 'file';

        $params = $this->parseFileParams($matches[2]);

        return $this->generateFileHtml($params, $type);
    }


    protected function parseFileParams(string $paramString): array
    {
        $params = [];

        if (preg_match('/title\(([^)]+)\)/', $paramString, $m)) {
            $params['title'] = $m[1];
        }

        if (preg_match('/id\((\d+)\)/', $paramString, $m)) {
            $params['id'] = (int)$m[1];
        }

        if (preg_match('/dl-link(?:\(([^)]*)\))?/', $paramString, $m)) {
            $params['dl_link'] = $m[1] ?? null;
        }

        if (preg_match('/open-link(?:\(([^)]*)\))?/', $paramString, $m)) {
            $params['open_link'] = $m[1] ?? null;
        }

        if (preg_match('/dl-text\(([^)]*)\)/', $paramString, $m)) {
            $params['dl_text'] = $m[1] ?: null;
        }

        if (preg_match('/open-text\(([^)]*)\)/', $paramString, $m)) {
            $params['open_text'] = $m[1] ?: null;
        }

        if (preg_match_all('/\.([\w\-]+)/', $paramString, $m)) {
            $params['classes'] = $m[1];
        }

        if (preg_match('/#([\w\-]+)/', $paramString, $m)) {
            $params['container_id'] = $m[1];
        }

        if (preg_match_all('/@([\w\-]+)\(([^)]*)\)/', $paramString, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $params[$match[1]] = $match[2] ?: $match[1];
            }
        }

        return $params;
    }


    protected function generateFileHtml(array $params, string $type): string
    {
        if (!isset($params['id'])) {
            return '';
        }

        $file = match($type) {
            'file' => PostFile::find($params['id']),
            'image' => PostImage::find($params['id'])
        };

        if (!$file) {
            return '';
        }

        $fileUrl = match($type) {
            'file' => $file->getFileUrl(),
            'image' => $file->getImageUrl('original')
        };

        $extension = $file->file_extension ? '.'.$file->file_extension : '';


        // TITLE
        $title = '';

        if (isset($params['title'])) {

            $title = match($params['title']) {

                '@title',
                '@title@ext'
                    => $file->title.$extension,

                '@name'
                    => $file->name,

                '@name@ext'
                    => $file->name.$extension,

                default
                    => $params['title']
            };
        }


        // CONTAINER CLASS
        $containerClass = 'file-links';

        if (!empty($params['classes'])) {
            $containerClass .= ' '.implode(' ', $params['classes']);
        }


        // CONTAINER ATTRS
        $containerAttrs = '';

        if (!empty($params['container_id'])) {
            $containerAttrs .= ' id="'.htmlspecialchars($params['container_id']).'"';
        }

        foreach ($params as $key => $value) {

            if (!in_array($key, [
                'id',
                'title',
                'classes',
                'container_id',
                'dl_link',
                'open_link',
                'dl_text',
                'open_text'
            ])) {

                $containerAttrs .= ' '
                    .htmlspecialchars($key)
                    .'="'
                    .htmlspecialchars($value)
                    .'"';
            }
        }


        $linkTarget = $params['target'] ?? '_blank';


        $html = '<div class="'.htmlspecialchars($containerClass).'"'.$containerAttrs.'>';


        // TITLE HTML
        if ($title) {

            $html .= '<span class="file-links-title">'
                .htmlspecialchars($title)
                .'</span>';
        }


        // OPEN LINK
        if (array_key_exists('open_link', $params)) {

            $openText = $params['open_text']
                ?? '<i class="mdi mdi-link-variant"></i> Megnyitás';

            $html .= '<a class="file-links-open"'
                .' href="'.htmlspecialchars($fileUrl).'"'
                .' target="'.$linkTarget.'">'
                .$openText
                .'</a>';
        }


        // DOWNLOAD LINK
        if (array_key_exists('dl_link', $params)) {

            $dlText = $params['dl_text']
                ?? '<i class="mdi mdi-arrow-down-bold"></i> Letöltés';

            $downloadFilename = $file->name.$extension;

            if ($params['dl_link'] === '@title') {
                $downloadFilename = $file->title.$extension;
            }
            elseif ($params['dl_link'] === '@name') {
                $downloadFilename = $file->name.$extension;
            }
            elseif (!empty($params['dl_link'])) {
                $downloadFilename = strpos($params['dl_link'], '.') === false
                    ? $params['dl_link'].$extension
                    : $params['dl_link'];
            }

            $html .= '<a class="file-links-dl"'
                .' href="'.htmlspecialchars($fileUrl).'"'
                .' target="'.$linkTarget.'"'
                .' download="'.htmlspecialchars($downloadFilename).'"'
                .' rel="noopener noreferrer">'
                .$dlText
                .'</a>';
        }


        $html .= '</div>';

        return $html;
    }
}