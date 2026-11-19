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
     * @var ?non-empty-string
     */
    private ?string $minorVersion;
    private ?PlannedMinorVersion $nextMinorVersion;
    private ?DateTimeImmutable $endOfBugfixSupport;
    private ?DateTimeImmutable $endOfLife;

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
     * @var list<non-empty-string>
     */
    private array $tutorialPhpVersions;

    /**
     * @param positive-int           $version
     * @param non-empty-string       $minimumPhpVersion
     * @param ?non-empty-string      $maximumPhpVersion
     * @param ?non-empty-string      $minorVersion
     * @param ?non-empty-string      $documentationUrl
     * @param ?positive-int          $milestone
     * @param list<non-empty-string> $tutorialPhpVersions
     */
    public function __construct(int $version, DateTimeImmutable $released, string $minimumPhpVersion, ?string $maximumPhpVersion, ?string $minorVersion, ?PlannedMinorVersion $nextMinorVersion, ?DateTimeImmutable $endOfBugfixSupport, ?DateTimeImmutable $endOfLife, ?string $documentationUrl, bool $hasDeprecationList, ?int $milestone, array $tutorialPhpVersions)
    {
        $this->version             = $version;
        $this->released            = $released;
        $this->minimumPhpVersion   = $minimumPhpVersion;
        $this->maximumPhpVersion   = $maximumPhpVersion;
        $this->minorVersion        = $minorVersion;
        $this->nextMinorVersion    = $nextMinorVersion;
        $this->endOfBugfixSupport  = $endOfBugfixSupport;
        $this->endOfLife           = $endOfLife;
        $this->documentationUrl    = $documentationUrl;
        $this->hasDeprecationList  = $hasDeprecationList;
        $this->milestone           = $milestone;
        $this->tutorialPhpVersions = $tutorialPhpVersions;
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
     * @phpstan-assert-if-true !null $this->minorVersion
     */
    public function hasMinorVersion(): bool
    {
        return $this->minorVersion !== null;
    }

    /**
     * The newest minor version that is released on the given day.
     *
     * @throws InvalidConfigurationException
     *
     * @return non-empty-string
     */
    public function minorVersion(DateTimeImmutable $today): string
    {
        if ($this->nextMinorVersion !== null && $this->nextMinorVersion->isReleased($today)) {
            return $this->nextMinorVersion->version();
        }

        if ($this->minorVersion === null) {
            throw new InvalidConfigurationException(
                sprintf(
                    'No minor version is configured for %s',
                    $this->name(),
                ),
            );
        }

        return $this->minorVersion;
    }

    /**
     * The minor version that is planned but not released on the given day.
     */
    public function nextMinorVersion(DateTimeImmutable $today): ?PlannedMinorVersion
    {
        if ($this->nextMinorVersion === null) {
            return null;
        }

        if ($this->nextMinorVersion->isReleased($today)) {
            return null;
        }

        return $this->nextMinorVersion;
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
        if ($this->documentationUrl !== null) {
            return $this->documentationUrl;
        }

        if ($this->minorVersion === null) {
            return null;
        }

        return sprintf('https://docs.phpunit.de/en/%s/', $this->minorVersion($today));
    }

    public function hasDeprecationList(): bool
    {
        return $this->hasDeprecationList;
    }

    /**
     * @return ?positive-int
     */
    public function milestone(): ?int
    {
        return $this->milestone;
    }

    /**
     * @return list<non-empty-string>
     */
    public function tutorialPhpVersions(): array
    {
        return $this->tutorialPhpVersions;
    }
}
