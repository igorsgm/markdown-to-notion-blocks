<?php

declare(strict_types=1);

use League\CommonMark\Extension\CommonMark\Node\Inline\Image as CommonMarkImage;
use RoelMR\MarkdownToNotionBlocks\NotionBlocks\Image;
use RoelMR\MarkdownToNotionBlocks\Validation\ImageValidator;

test('ImageValidator validates supported image extensions', function () {
    $validUrls = [
        'https://example.com/image.jpg',
        'https://example.com/image.jpeg',
        'https://example.com/image.png',
        'https://example.com/image.gif',
        'https://example.com/image.svg',
        'https://example.com/image.bmp',
        'https://example.com/image.tif',
        'https://example.com/image.tiff',
        'https://example.com/image.heic',
    ];

    foreach ($validUrls as $url) {
        expect(ImageValidator::isValidNotionImage($url))->toBeTrue("URL $url should be valid");
    }
});

test('ImageValidator rejects unsupported image extensions', function () {
    $invalidUrls = [
        'https://example.com/image.webp',
        'https://example.com/image.avif',
        'https://example.com/image.ico',
        'https://example.com/document.pdf',
        'https://example.com/video.mp4',
        'https://example.com/audio.mp3',
        'https://example.com/no-extension',
    ];

    foreach ($invalidUrls as $url) {
        expect(ImageValidator::isValidNotionImage($url))->toBeFalse("URL $url should be invalid");
    }
});

test('ImageValidator handles URLs with query parameters', function () {
    expect(ImageValidator::isValidNotionImage('https://example.com/image.jpg?v=1&size=large'))->toBeTrue();
    expect(ImageValidator::isValidNotionImage('https://example.com/image.webp?v=1&size=large'))->toBeFalse();
});

test('ImageValidator handles URLs with fragments', function () {
    expect(ImageValidator::isValidNotionImage('https://example.com/image.png#section'))->toBeTrue();
    expect(ImageValidator::isValidNotionImage('https://example.com/image.webp#section'))->toBeFalse();
});

test('ImageValidator handles case insensitive extensions', function () {
    expect(ImageValidator::isValidNotionImage('https://example.com/image.JPG'))->toBeTrue();
    expect(ImageValidator::isValidNotionImage('https://example.com/image.PNG'))->toBeTrue();
    expect(ImageValidator::isValidNotionImage('https://example.com/image.WEBP'))->toBeFalse();
});

test('ImageValidator allows non-external URLs', function () {
    $nonExternalUrls = [
        './images/local.jpg',
        '../assets/image.png',
        '/absolute/path/image.svg',
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mZ8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
    ];

    foreach ($nonExternalUrls as $url) {
        expect(ImageValidator::isValidNotionImage($url))->toBeTrue("URL $url should be valid");
    }
});

test('ImageValidator returns supported extensions list', function () {
    $extensions = ImageValidator::getSupportedExtensions();

    expect($extensions)->toBeArray()
        ->and($extensions)->toContain('jpg', 'jpeg', 'png', 'gif', 'svg', 'bmp', 'tif', 'tiff', 'heic');
});

test('Image block converts invalid images to paragraph blocks', function () {
    $imageNode = new CommonMarkImage('https://example.com/image.webp', 'Invalid image');
    $imageBlock = new Image($imageNode);

    $result = $imageBlock->object();

    expect($result['type'])->toBe('paragraph')
        ->and($result['paragraph']['rich_text'][0]['text']['content'])->toBe('[Invalid image: https://example.com/image.webp]')
        ->and($result['paragraph']['rich_text'][0]['annotations']['italic'])->toBeTrue()
        ->and($result['paragraph']['rich_text'][0]['annotations']['color'])->toBe('gray');
});

test('Image block preserves valid images', function () {
    $imageNode = new CommonMarkImage('https://example.com/image.jpg', 'Valid image');
    $imageBlock = new Image($imageNode);

    $result = $imageBlock->object();

    expect($result['type'])->toBe('image')
        ->and($result['image']['external']['url'])->toBe('https://example.com/image.jpg');
});

test('Image block handles mixed valid and invalid images in markdown', function () {
    $validMarkdown = '![Valid](https://example.com/image.jpg)';
    $invalidMarkdown = '![Invalid](https://example.com/image.webp)';

    $validResult = json_decode(convert($validMarkdown), true);
    $invalidResult = json_decode(convert($invalidMarkdown), true);

    expect($validResult[0][0]['type'])->toBe('image');
    expect($invalidResult[0][0]['type'])->toBe('paragraph');
    expect($invalidResult[0][0]['paragraph']['rich_text'][0]['text']['content'])->toContain('[Invalid image:');
});

test('ImageValidator handles malformed URLs gracefully', function () {
    $malformedUrls = [
        'not-a-url',
        'http://',
        'https://',
        'ftp://example.com/image.jpg',
        '',
    ];

    foreach ($malformedUrls as $url) {
        expect(ImageValidator::isValidNotionImage($url))->toBeTrue("Malformed URL $url should be treated as non-external");
    }
});

test('ImageValidator handles URLs without file extensions', function () {
    expect(ImageValidator::isValidNotionImage('https://example.com/image'))->toBeFalse();
    expect(ImageValidator::isValidNotionImage('https://example.com/path/to/image'))->toBeFalse();
});

test('ImageValidator handles URLs with multiple dots', function () {
    expect(ImageValidator::isValidNotionImage('https://sub.example.com/image.file.jpg'))->toBeTrue();
    expect(ImageValidator::isValidNotionImage('https://sub.example.com/image.file.webp'))->toBeFalse();
});
