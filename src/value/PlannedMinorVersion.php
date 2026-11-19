<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

/**
 * The minor version of a major version that follows the one that is
 * configured as its newest: it becomes the newest minor version on the day
 * of its release.
 */
final readonly class PlannedMinorVersion
{
    /**
     * @var non-empty-string
     */
    private string $version;
    private DateTimeImmutable $released;

    /**
     * @var positive-int
     */
    private int $milestone;

    /**
     * @param non-empty-string $version
     * @param positive-int     $milestone
     */
    public function __construct(string $version, DateTimeImmutable $released, int $milestone)
    {
        $this->version   = $version;
        $this->released  = $released;
        $this->milestone = $milestone;
    }

    /**
     * @return non-empty-string
     */
    public function version(): string
    {
        return $this->version;
    }

    public function released(): DateTimeImmutable
    {
        return $this->released;
    }

    public function isReleased(DateTimeImmutable $today): bool
    {
        return $this->released <= $today;
    }

    /**
     * @return positive-int
     */
    public function milestone(): int
    {
        return $this->milestone;
    }
}
