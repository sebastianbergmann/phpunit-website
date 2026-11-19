<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MajorVersion::class)]
#[UsesClass(PlannedMinorVersion::class)]
final class MajorVersionTest extends TestCase
{
    public function testHasVersionAndName(): void
    {
        $majorVersion = $this->majorVersion();

        $this->assertSame(13, $majorVersion->version());
        $this->assertSame('PHPUnit 13', $majorVersion->name());
    }

    public function testIsReleasedOnTheDayOfItsRelease(): void
    {
        $majorVersion = $this->majorVersion();

        $this->assertSame('2026-02-06', $majorVersion->released()->format('Y-m-d'));
        $this->assertFalse($majorVersion->isReleased(new DateTimeImmutable('2026-02-05')));
        $this->assertTrue($majorVersion->isReleased(new DateTimeImmutable('2026-02-06')));
    }

    public function testDescribesCompatibilityWithPhpWithoutUpperBound(): void
    {
        $majorVersion = $this->majorVersion();

        $this->assertSame('8.4', $majorVersion->minimumPhpVersion());
        $this->assertSame('>= PHP 8.4', $majorVersion->phpCompatibility());
    }

    public function testDescribesCompatibilityWithPhpWithUpperBound(): void
    {
        $majorVersion = $this->majorVersion(maximumPhpVersion: '8.5');

        $this->assertSame('PHP 8.4 - PHP 8.5', $majorVersion->phpCompatibility());
    }

    public function testNextMinorVersionReplacesMinorVersionOnTheDayOfItsRelease(): void
    {
        $majorVersion = $this->majorVersion(
            nextMinorVersion: new PlannedMinorVersion('13.5', new DateTimeImmutable('2026-12-04'), 74),
        );

        $dayBefore = new DateTimeImmutable('2026-12-03');
        $next      = $majorVersion->nextMinorVersion($dayBefore);

        $this->assertSame('13.4', $majorVersion->minorVersion($dayBefore));
        $this->assertNotNull($next);
        $this->assertSame('13.5', $next->version());

        $dayOfRelease = new DateTimeImmutable('2026-12-04');

        $this->assertSame('13.5', $majorVersion->minorVersion($dayOfRelease));
        $this->assertNull($majorVersion->nextMinorVersion($dayOfRelease));
    }

    public function testHasNoNextMinorVersionWhenNoneIsConfigured(): void
    {
        $this->assertNull($this->majorVersion()->nextMinorVersion(new DateTimeImmutable('2026-11-19')));
    }

    public function testMinorVersionCannotBeAskedForWhenNoneIsConfigured(): void
    {
        $majorVersion = $this->majorVersion(minorVersion: null);

        $this->assertFalse($majorVersion->hasMinorVersion());

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('No minor version is configured for PHPUnit 13');

        $majorVersion->minorVersion(new DateTimeImmutable('2026-11-19'));
    }

    public function testIsAtItsEndOfLifeOnTheConfiguredDay(): void
    {
        $majorVersion = $this->majorVersion(endOfLife: new DateTimeImmutable('2030-02-01'));

        $this->assertFalse($majorVersion->isEndOfLife(new DateTimeImmutable('2030-01-31')));
        $this->assertTrue($majorVersion->isEndOfLife(new DateTimeImmutable('2030-02-01')));
        $this->assertNotNull($majorVersion->endOfLife());
    }

    public function testIsNotAtItsEndOfLifeWhenNoneIsConfigured(): void
    {
        $majorVersion = $this->majorVersion();

        $this->assertNull($majorVersion->endOfLife());
        $this->assertFalse($majorVersion->isEndOfLife(new DateTimeImmutable('2099-01-01')));
    }

    public function testDocumentationIsDerivedFromTheMinorVersion(): void
    {
        $majorVersion = $this->majorVersion(
            nextMinorVersion: new PlannedMinorVersion('13.5', new DateTimeImmutable('2026-12-04'), 74),
        );

        $this->assertSame('https://docs.phpunit.de/en/13.4/', $majorVersion->documentationUrl(new DateTimeImmutable('2026-11-19')));
        $this->assertSame('https://docs.phpunit.de/en/13.5/', $majorVersion->documentationUrl(new DateTimeImmutable('2026-12-04')));
    }

    public function testConfiguredDocumentationWins(): void
    {
        $majorVersion = $this->majorVersion(documentationUrl: 'https://phpunit.de/manual/6.5/en/index.html');

        $this->assertSame('https://phpunit.de/manual/6.5/en/index.html', $majorVersion->documentationUrl(new DateTimeImmutable('2026-11-19')));
    }

    public function testHasNoDocumentationWithoutMinorVersionAndConfiguredUrl(): void
    {
        $this->assertNull($this->majorVersion(minorVersion: null)->documentationUrl(new DateTimeImmutable('2026-11-19')));
    }

    public function testHasWhatIsConfigured(): void
    {
        $endOfBugfixSupport = new DateTimeImmutable('2028-02-04');

        $majorVersion = $this->majorVersion(
            endOfBugfixSupport: $endOfBugfixSupport,
            hasDeprecationList: true,
            milestone: 75,
            tutorialPhpVersions: ['8.4', '8.5'],
        );

        $this->assertTrue($majorVersion->hasMinorVersion());
        $this->assertSame($endOfBugfixSupport, $majorVersion->configuredEndOfBugfixSupport());
        $this->assertTrue($majorVersion->hasDeprecationList());
        $this->assertSame(75, $majorVersion->milestone());
        $this->assertSame(['8.4', '8.5'], $majorVersion->tutorialPhpVersions());
    }

    /**
     * @param ?non-empty-string      $maximumPhpVersion
     * @param ?non-empty-string      $minorVersion
     * @param ?non-empty-string      $documentationUrl
     * @param ?positive-int          $milestone
     * @param list<non-empty-string> $tutorialPhpVersions
     */
    private function majorVersion(?string $maximumPhpVersion = null, ?string $minorVersion = '13.4', ?PlannedMinorVersion $nextMinorVersion = null, ?DateTimeImmutable $endOfBugfixSupport = null, ?DateTimeImmutable $endOfLife = null, ?string $documentationUrl = null, bool $hasDeprecationList = false, ?int $milestone = null, array $tutorialPhpVersions = []): MajorVersion
    {
        return new MajorVersion(
            13,
            new DateTimeImmutable('2026-02-06'),
            '8.4',
            $maximumPhpVersion,
            $minorVersion,
            $nextMinorVersion,
            $endOfBugfixSupport,
            $endOfLife,
            $documentationUrl,
            $hasDeprecationList,
            $milestone,
            $tutorialPhpVersions,
        );
    }
}
