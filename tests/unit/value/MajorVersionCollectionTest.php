<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_map;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MajorVersionCollection::class)]
#[UsesClass(MajorVersion::class)]
final class MajorVersionCollectionTest extends TestCase
{
    public function testSortsMajorVersionsNewestFirst(): void
    {
        $this->assertSame([14, 13, 12, 11, 7], $this->versions($this->collection()->asArray()));
    }

    public function testRejectsMajorVersionThatIsConfiguredMoreThanOnce(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('PHPUnit 13 is configured more than once');

        MajorVersionCollection::fromList(
            [
                $this->majorVersion(13, '2026-02-06'),
                $this->majorVersion(13, '2026-02-06'),
            ],
        );
    }

    public function testCurrentMajorVersionIsTheNewestReleasedOne(): void
    {
        $collection = $this->collection();

        $this->assertSame(13, $collection->current(new DateTimeImmutable('2027-02-04'))->version());
        $this->assertSame(14, $collection->current(new DateTimeImmutable('2027-02-05'))->version());
    }

    public function testThereIsNoCurrentMajorVersionBeforeTheFirstRelease(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('No major version is released on 2000-01-01');

        $this->collection()->current(new DateTimeImmutable('2000-01-01'));
    }

    public function testNextMajorVersionIsTheOneThatIsReleasedNext(): void
    {
        $collection = $this->collection();

        $next = $collection->next(new DateTimeImmutable('2027-02-04'));

        $this->assertNotNull($next);
        $this->assertSame(14, $next->version());
        $this->assertNull($collection->next(new DateTimeImmutable('2027-02-05')));
    }

    public function testNextMajorVersionIsTheOldestOneThatIsNotReleased(): void
    {
        $next = $this->collection()->next(new DateTimeImmutable('2025-01-01'));

        $this->assertNotNull($next);
        $this->assertSame(12, $next->version());
    }

    public function testListsReleasedMajorVersions(): void
    {
        $this->assertSame([13, 12, 11, 7], $this->versions($this->collection()->released(new DateTimeImmutable('2026-11-19'))));
    }

    public function testFindsMajorVersion(): void
    {
        $collection = $this->collection();

        $this->assertTrue($collection->has(12));
        $this->assertFalse($collection->has(15));
        $this->assertSame(12, $collection->get(12)->version());
    }

    public function testMajorVersionThatIsNotConfiguredCannotBeAskedFor(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageIsOrContains('PHPUnit 15 is not configured');

        $this->collection()->get(15);
    }

    public function testBugfixSupportEndsWhenTheMajorVersionAfterTheNextOneIsReleased(): void
    {
        $collection = $this->collection();

        $this->assertSame('2026-02-06', $collection->endOfBugfixSupport($collection->get(11))->format('Y-m-d'));
        $this->assertSame('2027-02-05', $collection->endOfBugfixSupport($collection->get(12))->format('Y-m-d'));
    }

    public function testBugfixSupportIsExpectedToEndOnTheFirstFridayOfFebruaryTwoYearsAfterTheRelease(): void
    {
        $collection = $this->collection();

        $this->assertSame('2028-02-04', $collection->endOfBugfixSupport($collection->get(13))->format('Y-m-d'));
        $this->assertSame('2029-02-02', $collection->endOfBugfixSupport($collection->get(14))->format('Y-m-d'));
    }

    public function testConfiguredEndOfBugfixSupportWins(): void
    {
        $collection = $this->collection();

        $this->assertSame('2019-12-06', $collection->endOfBugfixSupport($collection->get(7))->format('Y-m-d'));
    }

    public function testStatusFollowsFromTheDates(): void
    {
        $collection = $this->collection();
        $today      = new DateTimeImmutable('2026-11-19');

        $this->assertSame(SupportStatus::BugfixSupport, $collection->status($collection->get(13), $today));
        $this->assertSame(SupportStatus::BugfixSupport, $collection->status($collection->get(12), $today));
        $this->assertSame(SupportStatus::LifeSupport, $collection->status($collection->get(11), $today));
        $this->assertSame(SupportStatus::EndOfLife, $collection->status($collection->get(7), $today));
    }

    public function testBugfixSupportEndsOnTheDayOfTheRelease(): void
    {
        $collection = $this->collection();

        $this->assertSame(SupportStatus::BugfixSupport, $collection->status($collection->get(12), new DateTimeImmutable('2027-02-04')));
        $this->assertSame(SupportStatus::LifeSupport, $collection->status($collection->get(12), new DateTimeImmutable('2027-02-05')));
    }

    public function testSeparatesSupportedFromUnsupportedMajorVersions(): void
    {
        $collection = $this->collection();
        $today      = new DateTimeImmutable('2026-11-19');

        $this->assertSame([13, 12, 11], $this->versions($collection->supported($today)));
        $this->assertSame([7], $this->versions($collection->unsupported($today)));
    }

    private function collection(): MajorVersionCollection
    {
        return MajorVersionCollection::fromList(
            [
                $this->majorVersion(11, '2024-02-02'),
                $this->majorVersion(7, '2018-02-02', '2019-12-06', '2020-02-07'),
                $this->majorVersion(13, '2026-02-06'),
                $this->majorVersion(14, '2027-02-05'),
                $this->majorVersion(12, '2025-02-07'),
            ],
        );
    }

    /**
     * @param positive-int     $version
     * @param non-empty-string $released
     */
    private function majorVersion(int $version, string $released, ?string $endOfBugfixSupport = null, ?string $endOfLife = null): MajorVersion
    {
        return new MajorVersion(
            $version,
            new DateTimeImmutable($released),
            '8.4',
            null,
            $version . '.0',
            null,
            $this->date($endOfBugfixSupport),
            $this->date($endOfLife),
            null,
            false,
            null,
            [],
        );
    }

    private function date(?string $date): ?DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        return new DateTimeImmutable($date);
    }

    /**
     * @param list<MajorVersion> $majorVersions
     *
     * @return list<positive-int>
     */
    private function versions(array $majorVersions): array
    {
        return array_map(
            static fn (MajorVersion $majorVersion): int => $majorVersion->version(),
            $majorVersions,
        );
    }
}
