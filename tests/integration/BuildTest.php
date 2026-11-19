<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function assert;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function substr_count;
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
#[CoversClass(AnnouncementPagesRenderer::class)]
#[CoversClass(AnnouncementsRenderer::class)]
#[CoversClass(BackwardCompatibilityRenderer::class)]
#[CoversClass(CodeSprintsRenderer::class)]
#[CoversClass(DocumentationRenderer::class)]
#[CoversClass(Factory::class)]
#[CoversClass(GettingStartedRenderer::class)]
#[CoversClass(HomepageRenderer::class)]
#[CoversClass(PublicDirectory::class)]
#[CoversClass(Renderer::class)]
#[CoversClass(SitemapRenderer::class)]
#[CoversClass(SponsorsRenderer::class)]
#[CoversClass(StaticPageRenderer::class)]
#[CoversClass(SupportedVersionsRenderer::class)]
#[CoversClass(VerifyRenderer::class)]
#[CoversTrait(Formatting::class)]
#[UsesClass(Announcement::class)]
#[UsesClass(Article::class)]
#[UsesClass(CodeSprint::class)]
#[UsesClass(ConfigurationLoader::class)]
#[UsesClass(MajorVersion::class)]
#[UsesClass(MajorVersionCollection::class)]
#[UsesClass(MinorVersion::class)]
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
        mkdir($this->publicDirectory . '/code-sprints');

        file_put_contents($this->publicDirectory . '/announcements/phpunit-2.html', '');
        file_put_contents($this->publicDirectory . '/announcements/notice.html', '');
        file_put_contents($this->publicDirectory . '/announcements/later.html', '');
        file_put_contents($this->publicDirectory . '/excluded.html', '');

        mkdir($this->publicDirectory . '/excluded');

        file_put_contents($this->publicDirectory . '/excluded/page.html', '');
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

        foreach (['backward-compatibility', 'datenschutz', 'documentation', 'getting-started', 'impressum', 'index', 'newsletter', 'sponsoring', 'sponsors', 'supported-versions', 'verify', 'announcements/index', 'code-sprints/index', 'code-sprints/october-2022', 'code-sprints/may-2021', 'code-sprints/march-2017'] as $page) {
            $html = $this->page($page);

            $this->assertStringContainsString('<header class="site-header">', $html, $page);
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
        $this->assertMatchesRegularExpression('~<tr class="is-security">\s+<th scope="row"><a href="/announcements/phpunit-2.html">PHPUnit 2</a></th>\s+<td><span class="badge badge--warn">Life Support</span></td>\s+<td>2.10</td>~', $html);
        $this->assertStringNotContainsString('is planned for', $html);
        $this->assertStringContainsString("</label>\n\n            <h2 id=\"bugfix-support\">", $html);
    }

    public function testDocumentationIsLinkedForSupportedAndNamedForUnsupportedVersions(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('documentation');

        $this->assertStringContainsString('<li><a href="https://docs.phpunit.de/en/2.3/" target="_blank" rel="noopener">PHPUnit 2.3</a></li>', $html);
        $this->assertStringContainsString('<li><code>https://example.org/manual/1.2/</code></li>', $html);
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
        $this->assertStringContainsString('This tutorial assumes that you use at least PHP 8.0.', $html);
        $this->assertStringContainsString('https://phar.phpunit.de/phpunit-2.phar', $html);
        $this->assertStringContainsString('PHPUnit 2.3.0 by Sebastian Bergmann and contributors.', $html);
        $this->assertStringContainsString('https://docs.phpunit.de/en/2.3/installation.html', $html);
        $this->assertStringContainsString('composer require --dev phpunit/phpunit ^2', $html);
    }

    public function testTutorialNamesTheOldestVersionOfPhpTheCurrentMajorVersionRunsOn(): void
    {
        $this->build('2023-02-03');

        $this->assertStringContainsString('This tutorial assumes that you use at least PHP 8.2.', $this->page('getting-started'));
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

    public function testCodeSprintThatIsNotOverIsTheNextEvent(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('code-sprints/index');

        $this->assertStringContainsString('<p>The next Code Sprint for PHPUnit will take place <a href="/code-sprints/october-2022.html">October 14-15, 2022 in Köln, Germany</a>.</p>', $html);
        $this->assertStringContainsString("            <ul>\n                <li><a href=\"/code-sprints/may-2021.html\">May 7-8, 2021</a></li>\n                <li><a href=\"/code-sprints/march-2017.html\">March 31, 2017 - April 1, 2017</a></li>\n            </ul>", $html);
        $this->assertStringContainsString('On October 14-15, 2022, there will be a code sprint in Köln, Germany to work on PHPUnit', $this->page('code-sprints/october-2022'));
    }

    public function testCodeSprintIsTheNextEventUntilItsLastDay(): void
    {
        $this->build('2022-10-15');

        $this->assertStringContainsString('will take place <a href="/code-sprints/october-2022.html">', $this->page('code-sprints/index'));
    }

    public function testCodeSprintThatIsOverIsAPastEvent(): void
    {
        $this->build('2022-10-16');

        $html = $this->page('code-sprints/index');

        $this->assertStringContainsString('<p>The next Code Sprint for PHPUnit is currently being planned and will be announced soon.</p>', $html);
        $this->assertStringContainsString("            <ul>\n                <li><a href=\"/code-sprints/october-2022.html\">October 14-15, 2022</a></li>\n                <li><a href=\"/code-sprints/may-2021.html\">", $html);
        $this->assertStringContainsString('On October 14-15, 2022, there was a code sprint in Köln, Germany to work on PHPUnit', $this->page('code-sprints/october-2022'));
    }

    public function testPageOfCodeSprintIsRenderedFromItsData(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('code-sprints/october-2022');

        $this->assertStringContainsString('<title>October 2022 Code Sprint for PHPUnit</title>', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://phpunit.de/code-sprints/october-2022.html">', $html);
        $this->assertStringContainsString('<h1>PHPUnit Code Sprint in Köln, Germany</h1>', $html);
        $this->assertStringContainsString('<p><time datetime="2022-10-14/2022-10-15">October 14-15, 2022</time></p>', $html);
        $this->assertStringContainsString('<p><a href="https://example.org/acme" target="_blank" rel="noopener"><img src="/img/sponsors/acme.svg" alt="Acme Inc." width="200" loading="lazy"></a></p>', $html);
        $this->assertStringContainsString('<p><a href="https://example.org/hoster" target="_blank" rel="noopener"><img src="/img/sponsors/hoster.svg" alt="Hoster" width="100" loading="lazy"></a></p>', $html);
        $this->assertStringContainsString("                <div>\n                </div>", $html);
    }

    public function testPageOfCodeSprintShowsItsImpressionsWhenThereAreSome(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('code-sprints/march-2017');

        $this->assertStringContainsString("                <div>\n                    <ul class=\"photo-grid\">", $html);
        $this->assertStringContainsString('<p><time datetime="2017-03-31/2017-04-01">March 31, 2017 - April 1, 2017</time></p>', $html);
    }

    public function testOrganizerOfCodeSprintNeedsLogoThatExists(): void
    {
        unlink($this->publicDirectory . '/img/sponsors/hoster.svg');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('The logo hoster.svg of Hoster does not exist in public/img/sponsors/');

        $this->build('2022-05-01');
    }

    public function testSitemapListsPagesThatAreNotExcluded(): void
    {
        $this->build('2022-05-01');

        $sitemap = file_get_contents($this->publicDirectory . '/sitemap.xml');

        $this->assertIsString($sitemap);
        $this->assertStringStartsWith("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n    <url>\n        <loc>https://phpunit.de/</loc>\n        <lastmod>2022-04-01</lastmod>\n    </url>\n    <url>\n        <loc>https://phpunit.de/announcements/</loc>\n", $sitemap);
        $this->assertStringEndsWith("    <url>\n        <loc>https://phpunit.de/verify.html</loc>\n        <lastmod>2022-04-01</lastmod>\n    </url>\n</urlset>\n", $sitemap);
        $this->assertStringContainsString('<loc>https://phpunit.de/announcements/phpunit-2.html</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://phpunit.de/impressum.html</loc>', $sitemap);
        $this->assertStringNotContainsString('index.html', $sitemap);
        $this->assertStringNotContainsString('excluded', $sitemap);
        $this->assertStringContainsString('<loc>https://phpunit.de/code-sprints/</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://phpunit.de/code-sprints/may-2021.html</loc>', $sitemap);
        $this->assertSame(19, substr_count($sitemap, '<url>'));
    }

    public function testSponsorNeedsLogoThatExists(): void
    {
        unlink($this->publicDirectory . '/img/sponsors/acme.svg');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('The logo acme.svg of Acme Inc. does not exist in public/img/sponsors/');

        $this->build('2022-05-01');
    }

    public function testThereMustBeAReleasedMajorVersion(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('No major version is released on 2018-01-01');

        $this->build('2018-01-01');
    }

    public function testReleaseOfMajorVersionGetsPageWhenItsTextExists(): void
    {
        $this->build('2026-03-01', 'announcement-pages');

        $html = $this->page('announcements/phpunit-13');

        $this->assertStringContainsString('<title>Release Announcement for Version 13 of PHPUnit</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="The PHPUnit development team is pleased to announce the immediate availability of PHPUnit 13. This release adds new features, changes and removes existing features, and fixes bugs.">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://phpunit.de/announcements/phpunit-13.html">', $html);
        $this->assertStringContainsString('<meta property="article:published_time" content="2026-02-06">', $html);
        $this->assertStringContainsString('"datePublished": "2026-02-06",', $html);
        $this->assertStringContainsString("        <main id=\"main\" class=\"wrap\">\n            <section class=\"announcement-hero\">", $html);
        $this->assertStringContainsString('<header class="site-header">', $html);
        $this->assertStringNotContainsString('{{', $html);
    }

    public function testPageForReleaseOfMajorVersionIsLinkedOnceItIsRendered(): void
    {
        $this->build('2026-03-01', 'announcement-pages');

        $this->assertStringContainsString('<a href="/announcements/phpunit-13.html">PHPUnit 13</a> is the', $this->page('index'));
        $this->assertStringContainsString('<h2><a href="/announcements/phpunit-13.html">PHPUnit 13</a></h2>', $this->page('announcements/index'));
    }

    public function testReleaseOfMajorVersionGetsNoPageBeforeTheDayOfTheRelease(): void
    {
        $this->build('2026-02-05', 'announcement-pages');

        $this->assertFileExists($this->publicDirectory . '/announcements/phpunit-12.html');
        $this->assertFileDoesNotExist($this->publicDirectory . '/announcements/phpunit-13.html');
    }

    public function testReleaseOfMajorVersionGetsNoPageWithoutText(): void
    {
        $this->build('2022-05-01');

        $this->assertFileDoesNotExist($this->publicDirectory . '/announcements/phpunit-1.html');
        $this->assertSame('', $this->page('announcements/phpunit-2'));
    }

    public function testOtherAnnouncementGetsPageWhenItsTextExists(): void
    {
        $this->build('2026-03-01', 'announcement-pages');

        $html = $this->page('announcements/end-of-life-for-pear-installation-method');

        $this->assertStringContainsString('<title>End of Life for PEAR Installation Method PHPUnit</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="PHPUnit can no longer be installed using the PEAR Installer.">', $html);
        $this->assertStringContainsString('<meta property="article:published_time" content="2014-04-21">', $html);
        $this->assertStringContainsString('<h1>End of Life for PEAR Installation Method</h1>', $html);
    }

    public function testAnnouncementsAreListedNewestFirst(): void
    {
        $this->build('2022-05-01');

        $html = $this->page('announcements/index');

        $this->assertMatchesRegularExpression('~>PHPUnit 2\.3</a>.*>Notice &amp; news</a>.*>PHPUnit 2</a>.*>PHPUnit 1\.2</a>.*>PHPUnit 1\.1</a>~s', $html);
        $this->assertStringNotContainsString('PHPUnit 2.4', $html);
        $this->assertStringNotContainsString('Not announced yet', $html);
        $this->assertStringNotContainsString('>PHPUnit 1</a>', $html);
        $this->assertStringContainsString("            </article>\n\n            <article class=\"archive-entry\">", $html);
        $this->assertStringContainsString("            </article>\n        </main>", $html);
    }

    public function testReleaseOfMajorVersionIsAnnouncedWithItsPage(): void
    {
        $this->build('2022-05-01');

        $this->assertStringContainsString(
            "<h2><a href=\"/announcements/phpunit-2.html\">PHPUnit 2</a></h2>\n                <time datetime=\"2021-02-05\">February 5, 2021</time>\n                <p>The PHPUnit development team is pleased to announce the release of PHPUnit 2. This release introduces",
            $this->page('announcements/index'),
        );
    }

    public function testReleaseOfMinorVersionIsAnnouncedWithSectionOfChangeLog(): void
    {
        $this->build('2022-05-01');

        $this->assertStringContainsString(
            "<h2><a href=\"https://github.com/sebastianbergmann/phpunit/blob/2.3.0/ChangeLog-2.3.md#230---2021-08-06\" target=\"_blank\" rel=\"noopener\">PHPUnit 2.3</a></h2>\n                <time datetime=\"2021-08-06\">August 6, 2021</time>\n                <p>The PHPUnit development team is pleased to announce the release of PHPUnit 2.3. This update introduces",
            $this->page('announcements/index'),
        );
    }

    public function testReleaseOfMinorVersionIsAnnouncedWithReleaseOnGitHubAsOfPhpunit11(): void
    {
        $this->build('2024-05-01', 'releases-on-github');

        $this->assertStringContainsString(
            '<h2><a href="https://github.com/sebastianbergmann/phpunit/releases/tag/11.1.0" target="_blank" rel="noopener">PHPUnit 11.1</a></h2>',
            $this->page('announcements/index'),
        );
    }

    public function testConfiguredAnnouncementOfMinorVersionWins(): void
    {
        $this->build('2022-05-01');

        $this->assertStringContainsString(
            '<h2><a href="https://example.org/1.2.html" target="_blank" rel="noopener">PHPUnit 1.2</a></h2>',
            $this->page('announcements/index'),
        );
    }

    public function testOtherAnnouncementIsListedWithItsPage(): void
    {
        $this->build('2022-05-01');

        $this->assertStringContainsString(
            "<h2><a href=\"/announcements/notice.html\">Notice &amp; news</a></h2>\n                <time datetime=\"2021-06-01\">June 1, 2021</time>\n                <p>Something was announced.</p>",
            $this->page('announcements/index'),
        );
    }

    public function testAnnouncementsAppearOnTheirDay(): void
    {
        $this->build('2022-12-02');

        $html = $this->page('announcements/index');

        $this->assertMatchesRegularExpression('~>PHPUnit 2\.10</a>.*>Not announced yet</a>.*>PHPUnit 2\.4</a>.*>PHPUnit 2\.3</a>~s', $html);
    }

    public function testOfTwoReleasesOnTheSameDayTheOneOfTheNewerVersionComesFirst(): void
    {
        file_put_contents($this->publicDirectory . '/announcements/phpunit-3.html', '');

        $this->build('2023-02-03', 'releases-on-the-same-day');

        $this->assertMatchesRegularExpression('~>PHPUnit 3</a>.*>PHPUnit 2\.1</a>~s', $this->page('announcements/index'));
    }

    public function testOtherAnnouncementNeedsPageThatExists(): void
    {
        unlink($this->publicDirectory . '/announcements/notice.html');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('The page notice.html of the announcement "Notice & news" does not exist in public/announcements/');

        $this->build('2022-05-01');
    }

    public function testHomepageNeedsMilestoneOfMinorVersionThatIsNotReleased(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('PHPUnit 1.1 is not released on 2019-03-01 and needs a milestone');

        $this->build('2019-03-01', 'no-milestone-for-minor-version');
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
        $pageHistory = $this->createStub(PageHistory::class);

        $pageHistory
            ->method('lastModified')
            ->willReturn(new DateTimeImmutable('2022-04-01'))
            ->seal();

        $factory = new Factory(
            new DateTimeImmutable($today),
            __DIR__ . '/../fixtures/configuration/' . $configuration . '.php',
            __DIR__ . '/../../templates',
            $this->publicDirectory,
            $pageHistory,
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
