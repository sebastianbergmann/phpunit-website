<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_map;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationLoader::class)]
#[UsesClass(Announcement::class)]
#[UsesClass(Article::class)]
#[UsesClass(MajorVersion::class)]
#[UsesClass(MajorVersionCollection::class)]
#[UsesClass(MinorVersion::class)]
#[UsesClass(Site::class)]
#[UsesClass(Sponsor::class)]
final class ConfigurationLoaderTest extends TestCase
{
    /**
     * @return non-empty-array<non-empty-string, array{non-empty-string, non-empty-string}>
     */
    public static function invalidConfigurations(): array
    {
        return [
            'file does not exist'                  => ['does-not-exist', 'does-not-exist.php does not exist'],
            'file does not return an array'        => ['not-an-array', 'does not return an array'],
            'unknown key'                          => ['unknown-key', 'configuration has the unknown key "sponsor" (known keys: majorVersions, announcements, articles, sponsors, sitemap)'],
            'no major versions'                    => ['no-major-versions', 'majorVersions must not be empty'],
            'unknown key in major version'         => ['unknown-key-in-major-version', 'majorVersions[0] has the unknown key "endOfLive"'],
            'invalid date'                         => ['invalid-date', 'majorVersions[0].released must be a date in the format YYYY-MM-DD, "2019-02-30" is not'],
            'minor version of other major version' => ['minor-version-of-other-major-version', 'majorVersions[0].minorVersions[0].version must be a minor version of PHPUnit 1, "2.1" is not'],
            'minor version is the first one'       => ['minor-version-is-first-one', 'majorVersions[0].minorVersions[0].version must not be "1.0": this is the release of the major version itself'],
            'duplicate minor version'              => ['duplicate-minor-version', 'PHPUnit 1.1 is configured more than once'],
            'unknown key in minor version'         => ['unknown-key-in-minor-version', 'majorVersions[0].minorVersions[0] has the unknown key "release"'],
            'invalid PHP version'                  => ['invalid-php-version', 'majorVersions[0].minimumPhpVersion must be a version number such as "8.4", "7" is not'],
            'duplicate major version'              => ['duplicate-major-version', 'PHPUnit 1 is configured more than once'],
            'missing sponsors'                     => ['missing-sponsors', 'sponsors must be an array'],
            'missing sitemap'                      => ['missing-sitemap', 'sitemap must be an array'],
            'exclusion from sitemap is not a path' => ['invalid-sitemap-exclusion', 'sitemap.exclude must be a list of paths'],
            'announcement without page'            => ['announcement-without-page', 'announcements[0].page must be a non-empty string'],
            'article without title'                => ['article-without-title', 'articles[0].title must be a non-empty string'],
            'milestone is not an integer'          => ['milestone-is-not-an-integer', 'majorVersions[0].milestone must be a positive integer'],
            'deprecations is not boolean'          => ['deprecations-is-not-boolean', 'majorVersions[0].deprecations must be true or false'],
            'major version is not an array'        => ['major-version-is-not-an-array', 'majorVersions must be a list of arrays'],
        ];
    }

    public function testLoadsMajorVersions(): void
    {
        $majorVersions = $this->load('valid')->majorVersions();
        $today         = new DateTimeImmutable('2022-05-01');

        $this->assertCount(3, $majorVersions->asArray());

        $current = $majorVersions->current($today);

        $this->assertSame(2, $current->version());
        $this->assertSame('2021-02-05', $current->released()->format('Y-m-d'));
        $this->assertSame('>= PHP 8.0', $current->phpCompatibility());
        $this->assertNull($current->endOfLife());
        $this->assertTrue($current->hasDeprecationList());
        $this->assertNull($current->milestone());
        $this->assertSame('https://docs.phpunit.de/en/2.3/', $current->documentationUrl($today));
    }

    public function testLoadsMinorVersionsAndSortsThemByVersion(): void
    {
        $current = $this->load('valid')->majorVersions()->get(2);

        $minorVersions = $current->releasedMinorVersions(new DateTimeImmutable('2099-01-01'));

        $this->assertSame(
            ['2.3', '2.4', '2.10'],
            array_map(
                static fn (MinorVersion $minorVersion): string => $minorVersion->version(),
                $minorVersions,
            ),
        );

        $this->assertCount(3, $minorVersions);
        $this->assertSame('2021-08-06', $minorVersions[0]->released()->format('Y-m-d'));
        $this->assertNull($minorVersions[0]->milestone());
        $this->assertNull($minorVersions[0]->configuredAnnouncementUrl());
        $this->assertSame(7, $minorVersions[1]->milestone());
    }

    public function testLoadsWhatIsOptionalForMajorVersion(): void
    {
        $majorVersions = $this->load('valid')->majorVersions();
        $today         = new DateTimeImmutable('2022-05-01');

        $oldest = $majorVersions->get(1);
        $newest = $majorVersions->get(3);

        $endOfBugfixSupport = $oldest->configuredEndOfBugfixSupport();
        $endOfLife          = $oldest->endOfLife();

        $this->assertSame('PHP 7.2 - PHP 7.4', $oldest->phpCompatibility());
        $this->assertNotNull($endOfBugfixSupport);
        $this->assertSame('2020-12-31', $endOfBugfixSupport->format('Y-m-d'));
        $this->assertNotNull($endOfLife);
        $this->assertSame('2021-12-31', $endOfLife->format('Y-m-d'));
        $this->assertSame('https://example.org/manual/1.2/', $oldest->documentationUrl($today));
        $minorVersions = $oldest->releasedMinorVersions($today);

        $this->assertCount(2, $minorVersions);
        $this->assertSame('https://example.org/1.2.html', $minorVersions[1]->configuredAnnouncementUrl());
        $this->assertFalse($oldest->hasDeprecationList());
        $this->assertSame(9, $newest->milestone());
        $this->assertSame([], $newest->releasedMinorVersions(new DateTimeImmutable('2099-01-01')));
    }

    public function testMajorVersionCanBeConfiguredToHaveNoDocumentation(): void
    {
        $majorVersion = $this->load('no-documentation')->majorVersions()->get(1);

        $this->assertNull($majorVersion->documentationUrl(new DateTimeImmutable('2022-05-01')));
    }

    public function testLoadsAnnouncements(): void
    {
        $announcements = $this->load('valid')->announcements();

        $this->assertCount(2, $announcements);
        $this->assertSame('2021-06-01', $announcements[0]->date()->format('Y-m-d'));
        $this->assertSame('Notice & news', $announcements[0]->title());
        $this->assertSame('notice.html', $announcements[0]->page());
        $this->assertSame('Something was announced.', $announcements[0]->text());
    }

    public function testLoadsArticles(): void
    {
        $articles = $this->load('valid')->articles();

        $this->assertCount(3, $articles);
        $this->assertSame('2022-03-01', $articles[1]->date()->format('Y-m-d'));
        $this->assertSame('Tips & tricks', $articles[1]->title());
        $this->assertSame('https://example.org/tips.html?a=1&b=2', $articles[1]->url());
    }

    public function testLoadsSponsors(): void
    {
        $site = $this->load('valid');

        $this->assertCount(1, $site->businessSponsors());
        $this->assertSame('Acme Inc.', $site->businessSponsors()[0]->name());
        $this->assertSame('https://example.org/acme', $site->businessSponsors()[0]->url());
        $this->assertSame('acme.svg', $site->businessSponsors()[0]->logo());
        $this->assertCount(1, $site->infrastructureSponsors());
        $this->assertSame('Hoster', $site->infrastructureSponsors()[0]->name());
    }

    public function testLoadsPagesThatAreExcludedFromTheSitemap(): void
    {
        $this->assertSame(['excluded.html', 'excluded/'], $this->load('valid')->pagesExcludedFromSitemap());
    }

    /**
     * @param non-empty-string $fixture
     * @param non-empty-string $message
     */
    #[DataProvider('invalidConfigurations')]
    public function testRejectsInvalidConfiguration(string $fixture, string $message): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains($message);

        $this->load($fixture);
    }

    public function testLoadsTheConfigurationOfTheWebsite(): void
    {
        $site = new ConfigurationLoader()->load(__DIR__ . '/../../../config.php');

        $this->assertTrue($site->majorVersions()->has(13));
    }

    /**
     * @param non-empty-string $fixture
     */
    private function load(string $fixture): Site
    {
        return new ConfigurationLoader()->load(__DIR__ . '/../../fixtures/configuration/' . $fixture . '.php');
    }
}
