<?php

namespace App\Support;

use Filament\Forms\Components\RichEditor\RichContentRenderer;

/**
 * Converts the CMS article editor output into safe public HTML. The editor
 * value is still treated as untrusted because a browser request can be
 * modified outside of Filament.
 */
final class ArticleRichText
{
    /** @var list<string> */
    private const LINK_PROTOCOLS = ['https', 'mailto', 'tel'];

    public static function toSafeHtml(mixed $content): string
    {
        if (! is_string($content) || trim($content) === '') {
            return '';
        }

        return RichContentRenderer::make(self::asHtml($content))
            ->linkProtocols(self::LINK_PROTOCOLS)
            ->toHtml();
    }

    /** Editor-ready HTML: plain text with blank-line paragraphs is wrapped in <p> tags. */
    public static function asHtml(string $content): string
    {
        if (preg_match('/<\\/?[a-z][^>]*>/i', $content) === 1) {
            return $content;
        }

        $paragraphs = preg_split('/\\R{2,}/', trim($content)) ?: [];

        return implode('', array_map(
            static fn (string $paragraph): string => '<p>'.nl2br(e(trim($paragraph)), use_xhtml: false).'</p>',
            array_filter($paragraphs, static fn (string $paragraph): bool => trim($paragraph) !== ''),
        ));
    }
}
