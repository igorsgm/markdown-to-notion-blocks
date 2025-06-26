<?php

declare(strict_types=1);

namespace RoelMR\MarkdownToNotionBlocks\Validation;

final class ImageValidator
{
    /**
     * Supported image file extensions according to Notion API.
     *
     * @see https://developers.notion.com/reference/block#image
     */
    private const SUPPORTED_EXTENSIONS = [
        'bmp',
        'gif',
        'heic',
        'jpeg',
        'jpg',
        'png',
        'svg',
        'tif',
        'tiff',
    ];

    /**
     * Validate if the image URL is valid for Notion.
     *
     * @since 1.0.0
     *
     * @param  string  $url  The image URL to validate.
     * @return bool True if the image is valid for Notion, false otherwise.
     */
    public static function isValidNotionImage(string $url): bool
    {
        // Skip validation for data URLs and non-external URLs
        if (!self::isExternalUrl($url)) {
            return true;
        }

        // Check if URL has a supported file extension
        return self::hasSupportedExtension($url);
    }

    /**
     * Get list of supported image extensions.
     *
     * @since 1.0.0
     *
     * @return array<string> Array of supported extensions.
     */
    public static function getSupportedExtensions(): array
    {
        return self::SUPPORTED_EXTENSIONS;
    }

    /**
     * Check if the URL is an external URL.
     *
     * @since 1.0.0
     *
     * @param  string  $url  The URL to check.
     * @return bool True if external URL, false otherwise.
     */
    private static function isExternalUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check if the URL has a supported file extension.
     *
     * @since 1.0.0
     *
     * @param  string  $url  The URL to check.
     * @return bool True if has supported extension, false otherwise.
     */
    private static function hasSupportedExtension(string $url): bool
    {
        // Parse URL to get path component
        $parsedUrl = parse_url($url);
        if ($parsedUrl === false || !isset($parsedUrl['path'])) {
            return false;
        }

        $path = $parsedUrl['path'];

        // Get file extension
        $extension = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::SUPPORTED_EXTENSIONS, true);
    }
}
