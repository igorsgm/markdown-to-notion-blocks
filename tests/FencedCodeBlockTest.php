<?php

declare(strict_types=1);

test('a fenced code has the expected output', function () {
    $markdown = <<<'MD'
    ```php
    echo 'Hello, World!';
    ```
    MD;

    $expected = [
        'object' => 'block',
        'type' => 'code',
        'code' => [
            'caption' => [],
            'rich_text' => [
                [
                    'type' => 'text',
                    'text' => [
                        'content' => "echo 'Hello, World!';",
                        'link' => null,
                    ],
                ],
            ],
            'language' => 'php',
        ],
    ];

    expect(convert($markdown))->toBe(expectedJson($expected));
});

test('a fenced code has an invalid language', function () {
    $markdown = <<<'MD'
    ```invalid
    echo 'Hello, World!';
    ```
    MD;

    $expected = [
        'object' => 'block',
        'type' => 'code',
        'code' => [
            'caption' => [],
            'rich_text' => [
                [
                    'type' => 'text',
                    'text' => [
                        'content' => "echo 'Hello, World!';",
                        'link' => null,
                    ],
                ],
            ],
            'language' => 'plain text',
        ],
    ];

    expect(convert($markdown))->toBe(expectedJson($expected));
});
