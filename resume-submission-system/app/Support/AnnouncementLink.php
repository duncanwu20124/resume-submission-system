<?php

namespace App\Support;

final class AnnouncementLink
{
    public static function normalizeUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    public static function displayTitle(?string $title, string $url): string
    {
        $title = trim((string) $title);

        return $title !== '' ? $title : $url;
    }

    public static function renderContent(string $content): string
    {
        $pattern = '/\[([^\]\r\n]+)\]\(([^)\r\n]+)\)/u';
        $rendered = '';
        $offset   = 0;

        while (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $fullMatch  = $matches[0][0];
            $matchStart = $matches[0][1];
            $url        = self::normalizeUrl($matches[2][0]);

            $rendered .= self::escape(substr($content, $offset, $matchStart - $offset));

            if ($url === null) {
                $rendered .= self::escape($matches[1][0]);
            } else {
                $rendered .= '<a href="' . self::escape($url) . '" target="_blank" rel="noopener noreferrer">'
                    . self::escape($matches[1][0])
                    . '</a>';
            }

            $offset = $matchStart + strlen($fullMatch);
        }

        return $rendered . self::escape(substr($content, $offset));
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
