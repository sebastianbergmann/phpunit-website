<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationLoader::class)]
#[UsesClass(Article::class)]
#[UsesClass(MajorVersion::class)]
#[UsesClass(MajorVersionCollection::class)]
#[UsesClass(PlannedMinorVersion::class)]
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
            'unknown key'                          => ['unknown-key', 'configuration has the unknown key "sponsor" (known keys: majorVersions, articles, sponsors)'],
            'no major versions'                    => ['no-major-versions', 'majorVersions must not be empty'],
            'unknown key in major version'         => ['unknown-key-in-major-version', 'majorVersions[0] has the unknown key "endOfLive"'],
            'invalid date'                         => ['invalid-date', 'majorVersions[0].released must be a date in the format YYYY-MM-DD, "2019-02-30" is not'],
            'missing minor version'                => ['missing-minor-version', 'majorVersions[0].minorVersion is required for a major version that has no endOfLife'],
            'minor version of other major version' => ['minor-version-of-other-major-version', 'majorVersions[0].minorVersion must be a minor version of PHPUnit 1, "2.0" is not'],
            'invalid PHP version'                  => ['invalid-php-version', 'majorVersions[0].minimumPhpVersion must be a version number such as "8.4", "7" is not'],
            'duplicate major version'              => ['duplicate-major-version', 'PHPUnit 1 is configured more than once'],
            'missing sponsors'                     => ['missing-sponsors', 'sponsors must be an array'],
            'article without title'                => ['article-without-title', 'articles[0].title must be a non-empty string'],
            'milestone is not an integer'          => ['milestone-is-not-an-integer', 'majorVersions[0].milestone must be a positive integer'],
            'deprecations is not boolean'          => ['deprecations-is-not-boolean', 'majorVersions[0].deprecations must be true or false'],
            'invalid PHP version for tutorial'     => ['invalid-tutorial-php-version', 'majorVersions[0].tutorialPhpVersions must be a list of PHP versions such as "8.4"'],
            'major version is not an array'        => ['major-version-is-not-an-array', 'majorVersions must be a list of arrays'],
        ];
    }

    public function testLoadsMajorVersions(): void
    {
        $majorVersions = $this->load('valid')->majorVersions();
        $today         = new DateTimeImmutable('2022-05-01');

        $this->assertCount(3, $majorVersions->asArray());

        $current = $majorVersions->current($today);
        $next    = $current->nextMinorVersion($today);

        $this->assertSame(2, $current->version());
        $this->assertSame('2021-02-05', $current->released()->format('Y-m-d'));
        $this->assertSame('>= PHP 8.0', $current->phpCompatibility());
        $this->assertSame('2.3', $current->minorVersion($today));
        $this->assertNotNull($next);
        $this->assertSame('2.4', $next->version());
        $this->assertSame('2022-06-03', $next->released()->format('Y-m-d'));
        $this->assertSame(7, $next->milestone());
        $this->assertNull($current->endOfLife());
        $this->assertTrue($current->hasDeprecationList());
        $this->assertNull($current->milestone());
        $this->assertSame(['8.0', '8.1'], $current->tutorialPhpVersions());
    }

    public function testLoadsWhatIsOptionalForMajorVersion(): void
    {
        $majorVersions = $this->load('valid')->majorVersions();

        $oldest = $majorVersions->get(1);
        $newest = $majorVersions->get(3);

        $endOfBugfixSupport = $oldest->configuredEndOfBugfixSupport();
        $endOfLife          = $oldest->endOfLife();

        $this->assertSame('PHP 7.2 - PHP 7.4', $oldest->phpCompatibility());
        $this->assertFalse($oldest->hasMinorVersion());
        $this->assertNotNull($endOfBugfixSupport);
        $this->assertSame('2020-12-31', $endOfBugfixSupport->format('Y-m-d'));
        $this->assertNotNull($endOfLife);
        $this->assertSame('2021-12-31', $endOfLife->format('Y-m-d'));
        $this->assertSame('https://example.org/manual/1.5/', $oldest->documentationUrl(new DateTimeImmutable('2022-05-01')));
        $this->assertFalse($oldest->hasDeprecationList());
        $this->assertSame([], $oldest->tutorialPhpVersions());
        $this->assertSame(9, $newest->milestone());
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
