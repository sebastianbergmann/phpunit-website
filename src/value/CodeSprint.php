<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function sprintf;
use function strtolower;
use DateTimeImmutable;

final readonly class CodeSprint
{
    private DateTimeImmutable $start;
    private DateTimeImmutable $end;

    /**
     * @var non-empty-string
     */
    private string $location;

    /**
     * @var list<Sponsor>
     */
    private array $organizers;

    /**
     * @var list<Sponsor>
     */
    private array $supporters;

    /**
     * @param non-empty-string $location
     * @param list<Sponsor>    $organizers
     * @param list<Sponsor>    $supporters
     */
    public function __construct(DateTimeImmutable $start, DateTimeImmutable $end, string $location, array $organizers, array $supporters)
    {
        $this->start      = $start;
        $this->end        = $end;
        $this->location   = $location;
        $this->organizers = $organizers;
        $this->supporters = $supporters;
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }

    public function isOver(DateTimeImmutable $today): bool
    {
        return $this->end < $today;
    }

    /**
     * @return non-empty-string
     */
    public function location(): string
    {
        return $this->location;
    }

    /**
     * @return list<Sponsor>
     */
    public function organizers(): array
    {
        return $this->organizers;
    }

    /**
     * @return list<Sponsor>
     */
    public function supporters(): array
    {
        return $this->supporters;
    }

    /**
     * The name of its page and of the file with its impressions, for
     * instance "april-2025": the month and the year it starts in.
     *
     * @return non-empty-string
     */
    public function slug(): string
    {
        return strtolower($this->start->format('F-Y'));
    }

    /**
     * @return non-empty-string
     */
    public function title(): string
    {
        return sprintf('%s Code Sprint for PHPUnit', $this->start->format('F Y'));
    }

    /**
     * "April 25, 2025", "April 25-26, 2025", or
     * "March 31, 2017 - April 1, 2017".
     *
     * @return non-empty-string
     */
    public function dates(): string
    {
        if ($this->start->format('Y-m-d') === $this->end->format('Y-m-d')) {
            return $this->start->format('F j, Y');
        }

        if ($this->start->format('Y-m') === $this->end->format('Y-m')) {
            return sprintf(
                '%s %s-%s, %s',
                $this->start->format('F'),
                $this->start->format('j'),
                $this->end->format('j'),
                $this->start->format('Y'),
            );
        }

        return sprintf(
            '%s - %s',
            $this->start->format('F j, Y'),
            $this->end->format('F j, Y'),
        );
    }
}
