<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function sprintf;
use DateTimeImmutable;

final readonly class MajorVersion
{
    /**
     * @var positive-int
     */
    private int $version;
    private DateTimeImmutable $released;

    /**
     * @var non-empty-string
     */
    private string $minimumPhpVersion;

    /**
     * @var ?non-empty-string
     */
    private ?string $maximumPhpVersion;

    /**
     * @var list<MinorVersion>
     */
    private array $minorVersions;
    private ?DateTimeImmutable $endOfBugfixSupport;
    private ?DateTimeImmutable $endOfLife;
    private bool $hasDocumentation;

    /**
     * @var ?non-empty-string
     */
    private ?string $documentationUrl;
    private bool $hasDeprecationList;

    /**
     * @var ?positive-int
     */
    private ?int $milestone;

    /**
     * @param positive-int       $version
     * @param non-empty-string   $minimumPhpVersion
     * @param ?non-empty-string  $maximumPhpVersion
     * @param list<MinorVersion> $minorVersions     the oldest one first
     * @param ?non-empty-string  $documentationUrl
     * @param ?positive-int      $milestone
     */
    public function __construct(int $version, DateTimeImmutable $released, string $minimumPhpVersion, ?string $maximumPhpVersion, array $minorVersions, ?DateTimeImmutable $endOfBugfixSupport, ?DateTimeImmutable $endOfLife, bool $hasDocumentation, ?string $documentationUrl, bool $hasDeprecationList, ?int $milestone)
    {
        $this->version            = $version;
        $this->released           = $released;
        $this->minimumPhpVersion  = $minimumPhpVersion;
        $this->maximumPhpVersion  = $maximumPhpVersion;
        $this->minorVersions      = $minorVersions;
        $this->endOfBugfixSupport = $endOfBugfixSupport;
        $this->endOfLife          = $endOfLife;
        $this->hasDocumentation   = $hasDocumentation;
        $this->documentationUrl   = $documentationUrl;
        $this->hasDeprecationList = $hasDeprecationList;
        $this->milestone          = $milestone;
    }

    /**
     * @return positive-int
     */
    public function version(): int
    {
        return $this->version;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return sprintf('PHPUnit %d', $this->version);
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
     * @return non-empty-string
     */
    public function minimumPhpVersion(): string
    {
        return $this->minimumPhpVersion;
    }

    /**
     * @return non-empty-string
     */
    public function phpCompatibility(): string
    {
        if ($this->maximumPhpVersion === null) {
            return sprintf('>= PHP %s', $this->minimumPhpVersion);
        }

        return sprintf('PHP %s - PHP %s', $this->minimumPhpVersion, $this->maximumPhpVersion);
    }

    /**
     * The newest minor version that is released on the given day. This is
     * the first one, such as 13.0, as long as no other one is released.
     *
     * @return non-empty-string
     */
    public function minorVersion(DateTimeImmutable $today): string
    {
        $version = sprintf('%d.0', $this->version);

        foreach ($this->minorVersions as $minorVersion) {
            if ($minorVersion->isReleased($today)) {
                $version = $minorVersion->version();
            }
        }

        return $version;
    }

    /**
     * The minor version that is released next after the given day, null when
     * none is configured.
     */
    public function nextMinorVersion(DateTimeImmutable $today): ?MinorVersion
    {
        foreach ($this->minorVersions as $minorVersion) {
            if (!$minorVersion->isReleased($today)) {
                return $minorVersion;
            }
        }

        return null;
    }

    /**
     * The minor versions that are released on the given day, the oldest one
     * first.
     *
     * @return list<MinorVersion>
     */
    public function releasedMinorVersions(DateTimeImmutable $today): array
    {
        $released = [];

        foreach ($this->minorVersions as $minorVersion) {
            if ($minorVersion->isReleased($today)) {
                $released[] = $minorVersion;
            }
        }

        return $released;
    }

    /**
     * The end of bugfix support when it does not follow from the release of
     * the major version after the next one.
     */
    public function configuredEndOfBugfixSupport(): ?DateTimeImmutable
    {
        return $this->endOfBugfixSupport;
    }

    public function endOfLife(): ?DateTimeImmutable
    {
        return $this->endOfLife;
    }

    public function isEndOfLife(DateTimeImmutable $today): bool
    {
        if ($this->endOfLife === null) {
            return false;
        }

        return $this->endOfLife <= $today;
    }

    /**
     * The URL of the documentation for the newest minor version that is
     * released on the given day, null when there is no documentation.
     *
     * @return ?non-empty-string
     */
    public function documentationUrl(DateTimeImmutable $today): ?string
    {
        if (!$this->hasDocumentation) {
            return null;
        }

        if ($this->documentationUrl !== null) {
            return $this->documentationUrl;
        }

        return sprintf('https://docs.phpunit.de/en/%s/', $this->minorVersion($today));
    }

    public function hasDeprecationList(): bool
    {
        return $this->hasDeprecationList;
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
}
