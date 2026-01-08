<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Attribute\AsTwigFilter;

/**
 * Twig extension for processing Quill editor HTML output.
 *
 * This extension provides a filter to convert Quill's internal HTML format
 * (which includes editor UI elements and custom markup) into clean, semantic
 * HTML suitable for frontend display.
 */
class QuillExtension
{
    /**
     * Converts Quill editor's internal HTML format to clean, semantic HTML.
     *
     * This method performs the following transformations:
     * - Removes Quill UI elements (.ql-ui classes) such as language selector dropdowns
     * - Converts .ql-code-block-container elements with .ql-code-block children
     *   into semantic `<pre data-language="X">` elements
     * - Preserves all other HTML content unchanged
     * - Maintains code block language attributes for syntax highlighting
     *
     * The conversion process:
     * 1. Parses the input HTML into a DOM document
     * 2. Finds and removes all elements with class "ql-ui"
     * 3. Locates all .ql-code-block-container elements
     * 4. Extracts code blocks within each container
     * 5. Combines code block lines with newline separators
     * 6. Creates a `<pre>` element with the appropriate language attribute
     * 7. Replaces the original container with the clean `<pre>` element
     *
     * @param string|null $quillHtml The raw HTML output from the Quill editor.
     *                               May contain .ql-code-block-container divs,
     *                               .ql-ui elements, and other Quill-specific markup.
     *
     * @return string Clean, semantic HTML suitable for frontend display.
     *                Returns an empty string if input is null or empty.
     *                Code blocks are converted to `<pre data-language="X">` format.
     *                If no language is specified, defaults to "plaintext".
     *
     * @throws \DOMException if creating an element fails
     *
     * @example
     * Input (Quill format):
     * <div class="ql-code-block-container">
     *   <select class="ql-ui">...</select>
     *   <div class="ql-code-block" data-language="php">class Test {</div>
     *   <div class="ql-code-block" data-language="php">}</div>
     * </div>
     *
     * Output (Clean HTML):
     * <pre data-language="php">class Test {
     * }</pre>
     */
    #[AsTwigFilter(name: 'quill_to_html', isSafe: ['html'])]
    public function quillToHtml(?string $quillHtml): string
    {
        if (!$quillHtml) {
            return '';
        }

        $domDocument = new \DOMDocument();
        libxml_use_internal_errors(true); // Temporarily suppress errors because loadHTML() is notoriously noisy.
        $domDocument->loadHTML('<?xml encoding="utf-8" ?>'.$quillHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors(false); // Re-enable errors
        $domxPath = new \DOMXPath($domDocument);

        // Remove .ql-ui selectors
        $uiElements = $domxPath->query("//*[contains(@class, 'ql-ui')]");
        foreach ($uiElements as $uiElement) {
            $uiElement->parentNode->removeChild($uiElement);
        }

        // Convert .ql-code-block-container to <pre>
        $containers = $domxPath->query("//*[contains(@class, 'ql-code-block-container')]");
        foreach ($containers as $container) {
            $codeBlocks = $domxPath->query(".//*[contains(@class, 'ql-code-block')]", $container);

            if ($codeBlocks->length === 0) {
                continue;
            }

            $language = $codeBlocks->item(0)->getAttribute('data-language') ?: 'plaintext';
            $lines    = [];
            foreach ($codeBlocks as $codeBlock) {
                $lines[] = $codeBlock->textContent;
            }

            $pre = $domDocument->createElement('pre');
            $pre->setAttribute('data-language', $language);
            $pre->textContent = implode("\n", $lines);

            $container->parentNode->replaceChild($pre, $container);
        }

        return $domDocument->saveHTML();
    }
}
