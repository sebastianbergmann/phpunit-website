<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateRenderer::class)]
final class TemplateRendererTest extends TestCase
{
    public function testReplacesPlaceholderWhereItStands(): void
    {
        $this->assertSame(
            "before\nBLOCK\nafter value\n",
            $this->renderer()->render('fragment.html', ['block' => "BLOCK\n", 'inline' => 'value']),
        );
    }

    public function testPlaceholderThatStandsAloneOnLineLeavesNothingBehindWhenItsValueIsEmpty(): void
    {
        $this->assertSame(
            "before\nafter value\n",
            $this->renderer()->render('fragment.html', ['block' => '', 'inline' => 'value']),
        );
    }

    public function testRejectsValueThatHasNoPlaceholder(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessageIsOrContains('Template fragment.html has no placeholder {{unknown}}');

        $this->renderer()->render('fragment.html', ['block' => '', 'inline' => '', 'unknown' => '']);
    }

    public function testRejectsPlaceholderThatIsNotReplaced(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessageIsOrContains('Placeholder {{forgotten}} of template unresolved.html was not replaced');

        $this->renderer()->render('unresolved.html', []);
    }

    public function testRejectsTemplateThatDoesNotExist(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessageIsOrContains('does-not-exist.html does not exist');

        $this->renderer()->render('does-not-exist.html', []);
    }

    public function testPageGetsHeaderAndFooter(): void
    {
        $this->assertSame(
            "<nav>\n<a class=\"brand\" href=\"/\">Brand</a>\n<li><a href=\"/\">Home</a></li>\n<li><a href=\"/other.html\">Other</a></li>\n</nav>\n<main>content</main>\n<footer></footer>\n",
            $this->renderer()->renderPage('unlisted.html', ['content' => 'content']),
        );
    }

    public function testEntryOfNavigationThatLinksToPageIsMarkedAsCurrent(): void
    {
        $page = $this->renderer()->renderPage('other.html', ['content' => 'content']);

        $this->assertStringContainsString('<li><a href="/other.html" aria-current="page">Other</a></li>', $page);
        $this->assertStringContainsString('<li><a href="/">Home</a></li>', $page);
    }

    public function testHomepageIsLinkedWithoutItsFileName(): void
    {
        $page = $this->renderer()->renderPage('index.html', ['content' => 'content']);

        $this->assertStringContainsString('<li><a href="/" aria-current="page">Home</a></li>', $page);
        $this->assertStringContainsString('<a class="brand" href="/">Brand</a>', $page);
        $this->assertStringContainsString('<li><a href="/other.html">Other</a></li>', $page);
    }

    private function renderer(): TemplateRenderer
    {
        return new TemplateRenderer(__DIR__ . '/../../fixtures/templates');
    }
}
