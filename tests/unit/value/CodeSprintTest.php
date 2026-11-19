<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeSprint::class)]
#[UsesClass(Sponsor::class)]
final class CodeSprintTest extends TestCase
{
    public function testHasWhatIsConfigured(): void
    {
        $start      = new DateTimeImmutable('2025-04-25');
        $end        = new DateTimeImmutable('2025-04-26');
        $organizers = [new Sponsor('Acme Inc.', 'https://example.org/', 'acme.svg')];
        $supporters = [new Sponsor('Hoster', 'https://example.org/', 'hoster.svg')];

        $codeSprint = new CodeSprint($start, $end, 'Berlin, Germany', $organizers, $supporters);

        $this->assertSame($start, $codeSprint->start());
        $this->assertSame($end, $codeSprint->end());
        $this->assertSame('Berlin, Germany', $codeSprint->location());
        $this->assertSame($organizers, $codeSprint->organizers());
        $this->assertSame($supporters, $codeSprint->supporters());
    }

    public function testIsNamedAfterTheMonthAndTheYearItStartsIn(): void
    {
        $codeSprint = $this->codeSprint('2017-03-31', '2017-04-01');

        $this->assertSame('march-2017', $codeSprint->slug());
        $this->assertSame('March 2017 Code Sprint for PHPUnit', $codeSprint->title());
    }

    public function testIsOverOnTheDayAfterItsLastDay(): void
    {
        $codeSprint = $this->codeSprint('2025-04-25', '2025-04-26');

        $this->assertFalse($codeSprint->isOver(new DateTimeImmutable('2025-04-24')));
        $this->assertFalse($codeSprint->isOver(new DateTimeImmutable('2025-04-26')));
        $this->assertTrue($codeSprint->isOver(new DateTimeImmutable('2025-04-27')));
    }

    public function testDescribesDatesWithinOneMonth(): void
    {
        $this->assertSame('April 25-26, 2025', $this->codeSprint('2025-04-25', '2025-04-26')->dates());
    }

    public function testDescribesDatesAcrossTwoMonths(): void
    {
        $this->assertSame('March 31, 2017 - April 1, 2017', $this->codeSprint('2017-03-31', '2017-04-01')->dates());
    }

    public function testDescribesSingleDay(): void
    {
        $this->assertSame('April 25, 2025', $this->codeSprint('2025-04-25', '2025-04-25')->dates());
    }

    /**
     * @param non-empty-string $start
     * @param non-empty-string $end
     */
    private function codeSprint(string $start, string $end): CodeSprint
    {
        return new CodeSprint(new DateTimeImmutable($start), new DateTimeImmutable($end), 'Berlin, Germany', [], []);
    }
}
