<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_map;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MajorVersion::class)]
#[UsesClass(MinorVersion::class)]
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

    public function testNewestMinorVersionIsTheFirstOneAsLongAsNoOtherOneIsReleased(): void
    {
        $this->assertSame('13.0', $this->majorVersion()->minorVersion(new DateTimeImmutable('2026-04-02')));
        $this->assertSame('13.0', $this->majorVersion(minorVersions: [])->minorVersion(new DateTimeImmutable('2030-01-01')));
    }

    public function testNewestMinorVersionIsTheNewestOneThatIsReleased(): void
    {
        $majorVersion = $this->majorVersion();

        $this->assertSame('13.1', $majorVersion->minorVersion(new DateTimeImmutable('2026-04-03')));
        $this->assertSame('13.4', $majorVersion->minorVersion(new DateTimeImmutable('2026-12-03')));
        $this->assertSame('13.5', $majorVersion->minorVersion(new DateTimeImmutable('2026-12-04')));
    }

    public function testNextMinorVersionIsTheOneThatIsReleasedNext(): void
    {
        $majorVersion = $this->majorVersion();

        $next = $majorVersion->nextMinorVersion(new DateTimeImmutable('2026-12-03'));

        $this->assertNotNull($next);
        $this->assertSame('13.5', $next->version());
        $this->assertNull($majorVersion->nextMinorVersion(new DateTimeImmutable('2026-12-04')));
    }

    public function testListsMinorVersionsThatAreReleased(): void
    {
        $majorVersion = $this->majorVersion();

        $this->assertSame([], $majorVersion->releasedMinorVersions(new DateTimeImmutable('2026-04-02')));

        $this->assertSame(
            ['13.1', '13.4'],
            array_map(
                static fn (MinorVersion $minorVersion): string => $minorVersion->version(),
                $majorVersion->releasedMinorVersions(new DateTimeImmutable('2026-12-03')),
            ),
        );
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

    public function testDocumentationIsDerivedFromTheNewestMinorVersion(): void
    {
        $majorVersion = $this->majorVersion();

        $this->assertSame('https://docs.phpunit.de/en/13.4/', $majorVersion->documentationUrl(new DateTimeImmutable('2026-11-19')));
        $this->assertSame('https://docs.phpunit.de/en/13.5/', $majorVersion->documentationUrl(new DateTimeImmutable('2026-12-04')));
    }

    public function testConfiguredDocumentationWins(): void
    {
        $majorVersion = $this->majorVersion(documentationUrl: 'https://phpunit.de/manual/6.5/en/index.html');

        $this->assertSame('https://phpunit.de/manual/6.5/en/index.html', $majorVersion->documentationUrl(new DateTimeImmutable('2026-11-19')));
    }

    public function testCanHaveNoDocumentation(): void
    {
        $this->assertNull($this->majorVersion(hasDocumentation: false)->documentationUrl(new DateTimeImmutable('2026-11-19')));
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

        $this->assertSame($endOfBugfixSupport, $majorVersion->configuredEndOfBugfixSupport());
        $this->assertTrue($majorVersion->hasDeprecationList());
        $this->assertSame(75, $majorVersion->milestone());
        $this->assertSame(['8.4', '8.5'], $majorVersion->tutorialPhpVersions());
    }

    /**
     * @param ?non-empty-string      $maximumPhpVersion
     * @param ?list<MinorVersion>    $minorVersions
     * @param ?non-empty-string      $documentationUrl
     * @param ?positive-int          $milestone
     * @param list<non-empty-string> $tutorialPhpVersions
     */
    private function majorVersion(?string $maximumPhpVersion = null, ?array $minorVersions = null, ?DateTimeImmutable $endOfBugfixSupport = null, ?DateTimeImmutable $endOfLife = null, bool $hasDocumentation = true, ?string $documentationUrl = null, bool $hasDeprecationList = false, ?int $milestone = null, array $tutorialPhpVersions = []): MajorVersion
    {
        if ($minorVersions === null) {
            $minorVersions = [
                new MinorVersion('13.1', new DateTimeImmutable('2026-04-03'), null, null),
                new MinorVersion('13.4', new DateTimeImmutable('2026-10-02'), null, null),
                new MinorVersion('13.5', new DateTimeImmutable('2026-12-04'), 74, null),
            ];
        }

        return new MajorVersion(
            13,
            new DateTimeImmutable('2026-02-06'),
            '8.4',
            $maximumPhpVersion,
            $minorVersions,
            $endOfBugfixSupport,
            $endOfLife,
            $hasDocumentation,
            $documentationUrl,
            $hasDeprecationList,
            $milestone,
            $tutorialPhpVersions,
        );
    }
}
