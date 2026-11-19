<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

/**
 * A minor version of a major version other than its first one: PHPUnit 13.4,
 * but not PHPUnit 13.0, which is the release of the major version itself.
 */
final readonly class MinorVersion
{
    /**
     * @var non-empty-string
     */
    private string $version;
    private DateTimeImmutable $released;

    /**
     * @var ?positive-int
     */
    private ?int $milestone;

    /**
     * @var ?non-empty-string
     */
    private ?string $announcementUrl;

    /**
     * @param non-empty-string  $version
     * @param ?positive-int     $milestone
     * @param ?non-empty-string $announcementUrl
     */
    public function __construct(string $version, DateTimeImmutable $released, ?int $milestone, ?string $announcementUrl)
    {
        $this->version         = $version;
        $this->released        = $released;
        $this->milestone       = $milestone;
        $this->announcementUrl = $announcementUrl;
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
     * The number of the milestone on GitHub.
     *
     * @return ?positive-int
     */
    public function milestone(): ?int
    {
        return $this->milestone;
    }

    /**
     * The URL of the announcement when it is not where the announcements of
     * minor versions usually are.
     *
     * @return ?non-empty-string
     */
    public function configuredAnnouncementUrl(): ?string
    {
        return $this->announcementUrl;
    }
}
