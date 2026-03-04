<?php

namespace Modules\FocusCmsCoreShortcodes\Classes\Shortcodes;

use App\Services\Contracts\DynamicShortcodeInterface;

class CodeShortcode implements DynamicShortcodeInterface
{
    public function pattern(): string
    {
        return '/{{code:(\w+)}}(.*?){{\/code}}/s';
    }


    public function render(array $matches): string
    {
        $language = $matches[1];

        $code = htmlspecialchars(
            $matches[2],
            ENT_QUOTES | ENT_SUBSTITUTE
        );


        $openingTag = match ($language) {

            'php'
                => '<pre class="line-numbers"><code class="language-php">',

            'css'
                => '<pre class="line-numbers"><code class="language-css">',

            'js'
                => '<pre class="line-numbers"><code class="language-javascript">',

            'html'
                => '<pre class="line-numbers"><code class="language-markup">',

            'xml'
                => '<pre class="line-numbers"><code class="language-markup">',

            'yaml'
                => '<pre class="line-numbers"><code class="language-markup">',

            'bash'
                => '<pre class="line-numbers"><code class="language-bash">',

            'batch'
                => '<pre class="line-numbers"><code class="language-batch">',

            'treeview'
                => '<pre><code class="language-treeview">',

            default
                => '<pre class="line-numbers"><code class="language-'
                    .htmlspecialchars($language, ENT_QUOTES | ENT_SUBSTITUTE)
                    .'">'
        };


        $closingTag = '</code></pre>';


        return $openingTag
            .trim($code)
            ."\n"
            .$closingTag;
    }
}