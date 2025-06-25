<?php

declare(strict_types=1);

namespace RoelMR\MarkdownToNotionBlocks\Converter;

use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use RoelMR\MarkdownToNotionBlocks\Objects\ImageLikeLink;

final class MarkdownImageProcessor
{
    /**
     * Extract all Image nodes from within a block node.
     */
    public function extractImages(Node $node): array
    {
        $images = [];

        if ($node instanceof Image) {
            $images[] = $node;
        }

        // Check if this is a Link that's actually an image (preceded by !)
        if ($node instanceof Link && $this->isImageLink($node)) {
            $altText = $this->getImageAltText($node);
            $images[] = new ImageLikeLink($node, $altText);
        }

        if ($node->hasChildren()) {
            foreach ($node->children() as $child) {
                $images = array_merge($images, $this->extractImages($child));
            }
        }

        return $images;
    }

    /**
     * Check if a paragraph node contains only images (and whitespace).
     */
    public function containsOnlyImages(Node $node): bool
    {
        if (!$node->hasChildren()) {
            return false;
        }

        $hasImages = false;
        $textContent = '';

        foreach ($node->children() as $child) {
            if ($child instanceof Image) {
                $hasImages = true;
            } elseif ($child instanceof Link && $this->isImageLink($child)) {
                $hasImages = true;
            } elseif ($child instanceof Text) {
                $text = $child->getLiteral();
                // Remove the "!" that precedes image links
                $cleanText = preg_replace('/!\s*$/', '', $text);
                $textContent .= $cleanText;
            } else {
                // Check if this child has meaningful text content
                $text = $this->getTextContent($child);
                $textContent .= $text;
            }
        }

        // If we have images and the only text content is whitespace or "!", skip this paragraph
        if ($hasImages && mb_trim($textContent) === '') {
            return true;
        }

        return false;
    }

    /**
     * Check if a Link node is actually an image (preceded by ! in markdown).
     */
    private function isImageLink(Link $link): bool
    {
        // Check if the parent paragraph contains a Text node with "!" before this link
        $parent = $link->parent();
        if (!$parent) {
            return false;
        }

        $children = iterator_to_array($parent->children());
        $linkIndex = array_search($link, $children, true);

        if ($linkIndex > 0) {
            $previousNode = $children[$linkIndex - 1];
            if ($previousNode instanceof Text) {
                $text = $previousNode->getLiteral();

                return str_ends_with($text, '!');
            }
        }

        return false;
    }

    /**
     * Get the alt text for an image link.
     */
    private function getImageAltText(Link $link): string
    {
        $altText = '';
        foreach ($link->children() as $child) {
            if ($child instanceof Text) {
                $altText .= $child->getLiteral();
            }
        }

        return mb_trim($altText);
    }

    /**
     * Get text content from a node, recursively.
     */
    private function getTextContent(Node $node): string
    {
        $text = '';

        if (method_exists($node, 'getLiteral')) {
            $text .= $node->getLiteral();
        }

        if ($node->hasChildren()) {
            foreach ($node->children() as $child) {
                $text .= $this->getTextContent($child);
            }
        }

        return $text;
    }
}
