<?php

declare(strict_types=1);

namespace RoelMR\MarkdownToNotionBlocks\NotionBlocks;

use League\CommonMark\Extension\CommonMark\Node\Inline\Image as CommonMarkImage;
use RoelMR\MarkdownToNotionBlocks\Objects\ImageLikeLink;
use RoelMR\MarkdownToNotionBlocks\Objects\NotionBlock;
use RoelMR\MarkdownToNotionBlocks\Validation\ImageValidator;

final class Image extends NotionBlock
{
    /**
     * Image constructor.
     *
     * @since 1.0.0
     *
     * @param  CommonMarkImage|ImageLikeLink  $node  The image node.
     *
     * @see https://developers.notion.com/reference/block#image
     */
    public function __construct(public CommonMarkImage|ImageLikeLink $node) {}

    /**
     * {@inheritDoc}
     */
    public function object(): array
    {
        $url = $this->node->getUrl();
        $isExternalUrl = filter_var($url, FILTER_VALIDATE_URL) !== false;

        // Validate image for Notion compatibility
        if (!ImageValidator::isValidNotionImage($url)) {
            // Return a paragraph block with the image URL as text for invalid images
            return [
                'object' => 'block',
                'type' => 'paragraph',
                'paragraph' => [
                    'rich_text' => [
                        [
                            'type' => 'text',
                            'text' => [
                                'content' => '[Invalid image: '.$url.']',
                                'link' => null,
                            ],
                            'annotations' => [
                                'bold' => false,
                                'italic' => true,
                                'strikethrough' => false,
                                'underline' => false,
                                'code' => false,
                                'color' => 'gray',
                            ],
                        ],
                    ],
                ],
            ];
        }

        return [
            'object' => 'block',
            'type' => 'image',
            'image' => [
                'type' => $isExternalUrl ? 'external' : 'file',
                $isExternalUrl ? 'external' : 'file' => [
                    'url' => $url,
                ],
                'caption' => $this->caption(),
            ],
        ];
    }

    /**
     * Get the caption for the image.
     *
     * @since 1.0.0
     *
     * @return array The caption for the image.
     */
    protected function caption(): array
    {
        $title = $this->node->getTitle();

        if (empty($title)) {
            return [];
        }

        return [
            [
                'type' => 'text',
                'text' => [
                    'content' => $title,
                    'link' => null,
                ],
                'annotations' => [
                    'bold' => false,
                    'italic' => false,
                    'strikethrough' => false,
                    'underline' => false,
                    'code' => false,
                    'color' => 'default',
                ],
            ],
        ];
    }
}
