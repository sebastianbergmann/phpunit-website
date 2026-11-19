<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function assert;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Renders the templates of the website with a configuration of its own into
 * a temporary directory.
 */
#[CoversClass(BackwardCompatibilityRenderer::class)]
#[CoversClass(DocumentationRenderer::class)]
#[CoversClass(Factory::class)]
#[CoversClass(GettingStartedRenderer::class)]
#[CoversClass(HomepageRenderer::class)]
#[CoversClass(PublicDirectory::class)]
#[CoversClass(Renderer::class)]
#[CoversClass(SponsorsRenderer::class)]
#[CoversClass(StaticPageRenderer::class)]
#[CoversClass(SupportedVersionsRenderer::class)]
#[CoversClass(VerifyRenderer::class)]
#[CoversTrait(Formatting::class)]
#[UsesClass(Article::class)]
#[UsesClass(ConfigurationLoader::class)]
#[UsesClass(MajorVersion::class)]
#[UsesClass(MajorVersionCollection::class)]
#[UsesClass(PlannedMinorVersion::class)]
#[UsesClass(Site::class)]
#[UsesClass(Sponsor::class)]
#[UsesClass(TemplateRenderer::class)]
final class BuildTest extends TestCase
{
    /**
     * @var non-empty-string
     */
    private string $publicDirectory;

    protected function setUp(): void
    {
        $this->publicDirectory = sys_get_temp_dir() . '/' . uniqid('phpunit-website-');

        mkdir($this->publicDirectory . '/announcements', 0o777, true);
        mkdir($this->publicDirectory . '/img/sponsors', 0o777, true);

        file_put_contents($this->publicDirectory . '/announcements/phpunit-2.html', '');
        file_put_contents($this->publicDirectory . '/img/sponsors/acme.svg', '');
        file_put_contents($this->publicDirectory . '/img/sponsors/hoster.svg', '');
    }

    protected function tearDown(): void
    {
        $this->remove($this->publicDirectory);
    }

    public function testRendersEveryPage(): void
    {
        $this->build('2022-05-01');

        foreach (['backward-compatibility', 'datenschutz', 'documentation', 'getting-started', 'impressum', 'index', 'newsletter', 'sponsoring', 'sponsors', 'supported-versions', 'verify'] as $page) {
            $html = $this->page($page);

            $this->assertStringContainsString('<header class="site-header">', $html);
            $this->assertStringContainsString('<footer class="site-footer">', $html);
            $this->assertStringNotContainsString('{{', $html);
        }
    }

    public function testHomepageNamesCurrentAndNextVersions(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('index');

        $this->assertStringContainsString('"softwareVersion": "2"', $html);
        $this->assertStringContainsString('"softwareRequirements": "PHP 8.0 or later"', $html);
        $this->assertStringContainsString('<a href="/announcements/phpunit-2.html">PHPUnit 2</a> is the', $html);
        $this->assertStringContainsString('milestone/7" target="_blank" rel="noopener">PHPUnit 2.4</a> will be the next minor version.', $html);
        $this->assertStringContainsString('milestone/9" target="_blank" rel="noopener">PHPUnit 3</a> will be the next major version.', $html);
        $this->assertStringContainsString('<li><a href="/" aria-current="page">Home</a></li>', $html);
    }

    public function testHomepageListsAnnouncementsThatHaveAPage(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('index');

        $this->assertStringContainsString('<time datetime="2021-02-05">February 5, 2021</time>', $html);
        $this->assertStringContainsString('<a href="/announcements/phpunit-2.html">PHPUnit 2 has been released</a>', $html);
        $this->assertStringNotContainsString('PHPUnit 1 has been released', $html);
    }

    public function testHomepageListsPublishedArticlesNewestFirst(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('index');

        $this->assertStringContainsString('<a href="https://example.org/tips.html?a=1&amp;b=2" target="_blank" rel="noopener">Tips &amp; tricks</a>', $html);
        $this->assertStringNotContainsString('Not published yet', $html);
        $this->assertMatchesRegularExpression('/Tips &amp; tricks.*Older article/s', $html);
    }

    public function testHomepageSwitchesToNewVersionsOnTheDayOfTheirRelease(): void
    {
        $this->build('2023-02-03');

        $html = $this->page('index');

        $this->assertStringContainsString('"softwareVersion": "3"', $html);
        $this->assertStringContainsString('PHPUnit 3 is the <a href="/supported-versions.html">current stable</a> version.', $html);
        $this->assertStringNotContainsString('will be the next minor version', $html);
        $this->assertStringNotContainsString('will be the next major version', $html);
        $this->assertStringContainsString('Not published yet', $html);
    }

    public function testSupportedVersionsAreListedWithDerivedStatusAndDates(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('supported-versions');

        $this->assertMatchesRegularExpression(
            '~<tr class="is-supported">\s+<th scope="row"><a href="/announcements/phpunit-2.html">PHPUnit 2</a></th>\s+<td><span class="badge badge--ok">Bugfix Support</span></td>\s+<td>2.3</td>\s+<td>&gt;= PHP 8.0</td>\s+<td><time datetime="2021-02-05">February 5, 2021</time></td>\s+<td><time datetime="2023-02-03">February 3, 2023</time></td>\s+<td>To be determined</td>~',
            $html,
        );

        $this->assertMatchesRegularExpression(
            '~<tbody class="table-older">\s+<tr class="is-eol">\s+<th scope="row">PHPUnit 1</th>\s+<td><span class="badge badge--danger">End of Life</span></td>\s+<td>None</td>\s+<td>PHP 7.2 - PHP 7.4</td>\s+<td><time datetime="2019-02-01">February 1, 2019</time></td>\s+<td><time datetime="2020-12-31">December 31, 2020</time></td>\s+<td><time datetime="2021-12-31">December 31, 2021</time></td>~',
            $html,
        );

        $this->assertStringNotContainsString('<th scope="row">PHPUnit 3</th>', $html);
        $this->assertStringContainsString('PHPUnit 3</a> is planned for <time datetime="2023-02-03">February 3, 2023</time>. Bugfix Support for <span class="nowrap">PHPUnit 1</span> ends on that day.', $html);
    }

    public function testSupportedVersionsChangeOnTheDayANewMajorVersionIsReleased(): void
    {
        $this->build('2023-02-03');

        $html = $this->page('supported-versions');

        $this->assertStringContainsString('<th scope="row">PHPUnit 3</th>', $html);
        $this->assertMatchesRegularExpression('~<tr class="is-security">\s+<th scope="row"><a href="/announcements/phpunit-2.html">PHPUnit 2</a></th>\s+<td><span class="badge badge--warn">Life Support</span></td>\s+<td>2.4</td>~', $html);
        $this->assertStringNotContainsString('is planned for', $html);
        $this->assertStringContainsString("</label>\n\n            <h2 id=\"bugfix-support\">", $html);
    }

    public function testDocumentationIsLinkedForSupportedAndNamedForUnsupportedVersions(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('documentation');

        $this->assertStringContainsString('<li><a href="https://docs.phpunit.de/en/2.3/" target="_blank" rel="noopener">PHPUnit 2.3</a></li>', $html);
        $this->assertStringContainsString('<li><code>https://example.org/manual/1.5/</code></li>', $html);
        $this->assertStringNotContainsString('https://docs.phpunit.de/en/3.0/', $html);
    }

    public function testDeprecationsAreLinkedOnTheBranchOfTheNewestMinorVersion(): void
    {
        $this->build('2022-06-03');

        $html = $this->page('backward-compatibility');

        $this->assertStringContainsString('<a href="https://github.com/sebastianbergmann/phpunit/blob/2.4/DEPRECATIONS.md" target="_blank" rel="noopener">PHPUnit 2.4</a>', $html);
        $this->assertStringNotContainsString('blob/1.', $html);
        $this->assertStringNotContainsString('blob/3.0', $html);
    }

    public function testTutorialIsWrittenForTheCurrentMajorVersion(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('getting-started');

        $this->assertStringContainsString('<h1>Getting Started with PHPUnit 2</h1>', $html);
        $this->assertStringContainsString('This tutorial assumes that you use PHP 8.0 or PHP 8.1.', $html);
        $this->assertStringContainsString('https://phar.phpunit.de/phpunit-2.phar', $html);
        $this->assertStringContainsString('PHPUnit 2.3.0 by Sebastian Bergmann and contributors.', $html);
        $this->assertStringContainsString('https://docs.phpunit.de/en/2.3/installation.html', $html);
        $this->assertStringContainsString('composer require --dev phpunit/phpunit ^2', $html);
    }

    public function testTutorialNamesSingleVersionOfPhp(): void
    {
        $this->build('2023-02-03');

        $this->assertStringContainsString('This tutorial assumes that you use PHP 8.2.', $this->page('getting-started'));
    }

    public function testVerificationUsesTheCurrentMajorVersionAsExample(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('verify');

        $this->assertStringContainsString('We use PHPUnit 2 as an example', $html);
        $this->assertStringContainsString('phpunit-2.phar.asc', $html);
    }

    public function testSponsorsAreListed(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('sponsors');

        $this->assertStringContainsString('<li><a href="https://example.org/acme" target="_blank" rel="noopener"><img src="/img/sponsors/acme.svg" alt="Acme Inc." loading="lazy"></a></li>', $html);
        $this->assertStringContainsString('<li><a href="https://example.org/hoster" target="_blank" rel="noopener"><img src="/img/sponsors/hoster.svg" alt="Hoster" loading="lazy"></a></li>', $html);
        $this->assertStringContainsString('<li><a href="/sponsors.html" aria-current="page">Sponsors</a></li>', $html);
    }

    public function testSponsorNeedsLogoThatExists(): void
    {
        unlink($this->publicDirectory . '/img/sponsors/acme.svg');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('The logo acme.svg of sponsor Acme Inc. does not exist in public/img/sponsors/');

        $this->build('2022-05-01');
    }

    public function testThereMustBeAReleasedMajorVersion(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('No major version is released on 2018-01-01');

        $this->build('2018-01-01');
    }

    public function testHomepageNeedsMilestoneOfMajorVersionThatIsNotReleased(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('PHPUnit 2 is not released on 2020-06-01 and needs a milestone');

        $this->build('2020-06-01', 'no-milestone');
    }

    public function testSupportedVersionsNeedMilestoneOfMajorVersionThatIsNotReleased(): void
    {
        $renderer = new SupportedVersionsRenderer(
            new ConfigurationLoader()->load(__DIR__ . '/../fixtures/configuration/no-milestone.php'),
            new DateTimeImmutable('2020-06-01'),
            new TemplateRenderer(__DIR__ . '/../../templates'),
            new PublicDirectory($this->publicDirectory),
        );

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('PHPUnit 2 is not released on 2020-06-01 and needs a milestone');

        $renderer->render();
    }

    public function testTutorialNeedsVersionsOfPhpForTheCurrentMajorVersion(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('PHPUnit 1 is the current major version and needs tutorialPhpVersions');

        $this->build('2020-06-01', 'no-tutorial-php-versions');
    }

    public function testPageCannotBeWrittenToDirectoryThatDoesNotExist(): void
    {
        $publicDirectory = new PublicDirectory($this->publicDirectory . '/does-not-exist');

        $this->expectException(TemplateException::class);
        $this->expectExceptionMessageIsOrContains('Cannot write');

        @$publicDirectory->write('index.html', '');
    }

    /**
     * @param non-empty-string $today
     * @param non-empty-string $configuration
     */
    private function build(string $today, string $configuration = 'valid'): void
    {
        $factory = new Factory(
            new DateTimeImmutable($today),
            __DIR__ . '/../fixtures/configuration/' . $configuration . '.php',
            __DIR__ . '/../../templates',
            $this->publicDirectory,
        );

        $factory->renderer()->render();
    }

    /**
     * @param non-empty-string $page
     */
    private function page(string $page): string
    {
        $html = file_get_contents($this->publicDirectory . '/' . $page . '.html');

        assert($html !== false);

        return $html;
    }

    private function remove(string $directory): void
    {
        $entries = scandir($directory);

        assert($entries !== false);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . '/' . $entry;

            if (is_dir($path)) {
                $this->remove($path);

                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
