<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\QuillExtension;
use PHPUnit\Framework\TestCase;
use Twig\Attribute\AsTwigFilter;

/**
 * Tests for QuillExtension Twig filter.
 *
 * @covers \App\Twig\QuillExtension
 */
final class QuillExtensionTest extends TestCase
{
    private QuillExtension $quillExtension;

    protected function setUp(): void
    {
        $this->quillExtension = new QuillExtension();
    }

    public function testItReturnsEmptyStringForNullInput(): void
    {
        $result = $this->quillExtension->quillToHtml(null);

        $this->assertSame('', $result);
    }

    public function testItReturnsEmptyStringForEmptyString(): void
    {
        $result = $this->quillExtension->quillToHtml('');

        $this->assertSame('', $result);
    }

    public function testItConvertsSingleCodeBlockToPreElement(): void
    {
        $input = '<div class="ql-code-block-container"><div class="ql-code-block" data-language="php">echo "Hello";</div></div>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringContainsString('<pre data-language="php">echo "Hello";</pre>', $result);
    }

    public function testItConvertsMultilineCodeBlockWithNewlines(): void
    {
        $input = '<div class="ql-code-block-container">'.
            '<div class="ql-code-block" data-language="php">class Test {</div>'.
            '<div class="ql-code-block" data-language="php">    public function run() {</div>'.
            '<div class="ql-code-block" data-language="php">    }</div>'.
            '<div class="ql-code-block" data-language="php">}</div>'.
            '</div>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringContainsString('<pre data-language="php">class Test {
    public function run() {
    }
}</pre>', $result);
    }

    public function testItRemovesQlUiElements(): void
    {
        $input = '<div class="ql-code-block-container">'.
            '<select class="ql-ui"><option>PHP</option></select>'.
            '<div class="ql-code-block" data-language="php">echo "test";</div>'.
            '</div>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringNotContainsString('ql-ui', $result);
        $this->assertStringNotContainsString('<select', $result);
        $this->assertStringContainsString('<pre data-language="php">', $result);
    }

    public function testItDefaultsToPlaintextWhenNoLanguageSpecified(): void
    {
        $input = '<div class="ql-code-block-container">'.
            '<div class="ql-code-block">some code</div>'.
            '</div>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringContainsString('<pre data-language="plaintext">some code</pre>', $result);
    }

    public function testItHandlesMultipleCodeBlocksInSameDocument(): void
    {
        $input = '<p>Some text</p>'.
            '<div class="ql-code-block-container">'.
            '<div class="ql-code-block" data-language="php">echo "first";</div>'.
            '</div>'.
            '<p>More text</p>'.
            '<div class="ql-code-block-container">'.
            '<div class="ql-code-block" data-language="javascript">console.log("second");</div>'.
            '</div>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringContainsString('<pre data-language="php">echo "first";</pre>', $result);
        $this->assertStringContainsString('<pre data-language="javascript">console.log("second");</pre>', $result);
        $this->assertStringContainsString('<p>Some text</p>', $result);
        $this->assertStringContainsString('<p>More text</p>', $result);
    }

    public function testItPreservesRegularHtmlContent(): void
    {
        $input = '<h1>Title</h1><p>Paragraph with <strong>bold</strong> and <em>italic</em>.</p>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringContainsString('<h1>Title</h1>', $result);
        $this->assertStringContainsString('<p>Paragraph with <strong>bold</strong> and <em>italic</em>.</p>', $result);
    }

    public function testItHandlesEmptyCodeBlockContainer(): void
    {
        $input = '<div class="ql-code-block-container"></div><p>Text</p>';

        $result = $this->quillExtension->quillToHtml($input);

        // Empty container should be removed or left as-is, but shouldn't crash
        $this->assertStringContainsString('<p>Text</p>', $result);
    }

    public function testItHandlesSpecialCharactersInCode(): void
    {
        $input = '<div class="ql-code-block-container">'.
            '<div class="ql-code-block" data-language="php">$var = "&lt;script&gt;alert(\'xss\')&lt;/script&gt;";</div>'.
            '</div>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringContainsString('<pre data-language="php">', $result);
        // Entities should be preserved/decoded appropriately
        $this->assertStringContainsString('script', $result);
    }

    public function testItHandlesDifferentProgrammingLanguages(): void
    {
        $languages = ['php', 'javascript', 'python', 'rust', 'go', 'sql'];

        foreach ($languages as $language) {
            $input = sprintf(
                '<div class="ql-code-block-container"><div class="ql-code-block" data-language="%s">code</div></div>',
                $language
            );

            $result = $this->quillExtension->quillToHtml($input);

            $this->assertStringContainsString(sprintf('<pre data-language="%s">code</pre>', $language), $result);
        }
    }

    public function testItHandlesMixedContentWithParagraphsAndCode(): void
    {
        $input = '<p>Introduction paragraph.</p>'.
            '<p><br></p>'.
            '<div class="ql-code-block-container">'.
            '<div class="ql-code-block" data-language="php">function test() {}</div>'.
            '</div>'.
            '<p><br></p>'.
            '<p>Conclusion paragraph.</p>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringContainsString('<p>Introduction paragraph.</p>', $result);
        $this->assertStringContainsString('<pre data-language="php">function test() {}</pre>', $result);
        $this->assertStringContainsString('<p>Conclusion paragraph.</p>', $result);
    }

    public function testItHandlesCodeBlockWithEmptyLines(): void
    {
        $input = '<div class="ql-code-block-container">'.
            '<div class="ql-code-block" data-language="php">function test() {</div>'.
            '<div class="ql-code-block" data-language="php"></div>'.
            '<div class="ql-code-block" data-language="php">    return true;</div>'.
            '<div class="ql-code-block" data-language="php">}</div>'.
            '</div>';

        $result = $this->quillExtension->quillToHtml($input);

        $this->assertStringContainsString('<pre data-language="php">function test() {

    return true;
}</pre>', $result);
    }

    public function testItProvidesQuillToHtmlFilter(): void
    {
        $reflectionMethod = new \ReflectionMethod($this->quillExtension, 'quillToHtml');
        $attributes       = $reflectionMethod->getAttributes(AsTwigFilter::class);

        $this->assertCount(1, $attributes);

        $asTwigFilter = $attributes[0]->newInstance();
        $this->assertSame('quill_to_html', $asTwigFilter->name);
    }

    public function testItMarksFilterAsSafeForHtml(): void
    {
        $reflectionMethod = new \ReflectionMethod($this->quillExtension, 'quillToHtml');
        $attributes       = $reflectionMethod->getAttributes(AsTwigFilter::class);

        $this->assertCount(1, $attributes);

        $asTwigFilter = $attributes[0]->newInstance();
        $this->assertSame(['html'], $asTwigFilter->isSafe);
    }
}
