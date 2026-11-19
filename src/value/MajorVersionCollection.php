<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_reverse;
use function sprintf;
use function usort;
use DateTimeImmutable;

/**
 * The major versions of PHPUnit, the newest one first.
 *
 * What follows from the support policy is derived here instead of being
 * configured: when bugfix support ends, and which support a major version
 * has on a given day.
 */
final readonly class MajorVersionCollection
{
    /**
     * @var list<MajorVersion>
     */
    private array $majorVersions;

    /**
     * @param list<MajorVersion> $majorVersions
     *
     * @throws InvalidConfigurationException
     */
    public static function fromList(array $majorVersions): self
    {
        usort(
            $majorVersions,
            static fn (MajorVersion $a, MajorVersion $b): int => $b->version() <=> $a->version(),
        );

        $previous = null;

        foreach ($majorVersions as $majorVersion) {
            if ($previous !== null && $previous->version() === $majorVersion->version()) {
                throw new InvalidConfigurationException(
                    sprintf(
                        '%s is configured more than once',
                        $majorVersion->name(),
                    ),
                );
            }

            $previous = $majorVersion;
        }

        return new self($majorVersions);
    }

    /**
     * @param list<MajorVersion> $majorVersions
     */
    private function __construct(array $majorVersions)
    {
        $this->majorVersions = $majorVersions;
    }

    /**
     * @return list<MajorVersion>
     */
    public function asArray(): array
    {
        return $this->majorVersions;
    }

    /**
     * The major versions that are released on the given day, the newest one
     * first.
     *
     * @return list<MajorVersion>
     */
    public function released(DateTimeImmutable $today): array
    {
        $released = [];

        foreach ($this->majorVersions as $majorVersion) {
            if ($majorVersion->isReleased($today)) {
                $released[] = $majorVersion;
            }
        }

        return $released;
    }

    /**
     * The newest major version that is released on the given day.
     *
     * @throws InvalidConfigurationException
     */
    public function current(DateTimeImmutable $today): MajorVersion
    {
        $released = $this->released($today);

        if ($released === []) {
            throw new InvalidConfigurationException(
                sprintf(
                    'No major version is released on %s',
                    $today->format('Y-m-d'),
                ),
            );
        }

        return $released[0];
    }

    /**
     * The major version that is released next after the given day, null when
     * none is configured.
     */
    public function next(DateTimeImmutable $today): ?MajorVersion
    {
        foreach (array_reverse($this->majorVersions) as $majorVersion) {
            if (!$majorVersion->isReleased($today)) {
                return $majorVersion;
            }
        }

        return null;
    }

    public function has(int $version): bool
    {
        return $this->find($version) !== null;
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function get(int $version): MajorVersion
    {
        $majorVersion = $this->find($version);

        if ($majorVersion === null) {
            throw new InvalidConfigurationException(
                sprintf(
                    'PHPUnit %d is not configured',
                    $version,
                ),
            );
        }

        return $majorVersion;
    }

    /**
     * Bugfix support for a major version ends when the major version after
     * the next one is released. As long as that one is not configured, it is
     * expected on the first Friday of February two years after the release.
     */
    public function endOfBugfixSupport(MajorVersion $majorVersion): DateTimeImmutable
    {
        $configured = $majorVersion->configuredEndOfBugfixSupport();

        if ($configured !== null) {
            return $configured;
        }

        $successor = $this->find($majorVersion->version() + 2);

        if ($successor !== null) {
            return $successor->released();
        }

        return new DateTimeImmutable(
            sprintf(
                'first friday of february %d',
                (int) $majorVersion->released()->format('Y') + 2,
            ),
        );
    }

    public function status(MajorVersion $majorVersion, DateTimeImmutable $today): SupportStatus
    {
        if ($majorVersion->isEndOfLife($today)) {
            return SupportStatus::EndOfLife;
        }

        if ($today < $this->endOfBugfixSupport($majorVersion)) {
            return SupportStatus::BugfixSupport;
        }

        return SupportStatus::LifeSupport;
    }

    /**
     * The released major versions that are not at their end of life on the
     * given day, the newest one first.
     *
     * @return list<MajorVersion>
     */
    public function supported(DateTimeImmutable $today): array
    {
        $supported = [];

        foreach ($this->released($today) as $majorVersion) {
            if (!$majorVersion->isEndOfLife($today)) {
                $supported[] = $majorVersion;
            }
        }

        return $supported;
    }

    /**
     * The major versions that are at their end of life on the given day, the
     * newest one first.
     *
     * @return list<MajorVersion>
     */
    public function unsupported(DateTimeImmutable $today): array
    {
        $unsupported = [];

        foreach ($this->released($today) as $majorVersion) {
            if ($majorVersion->isEndOfLife($today)) {
                $unsupported[] = $majorVersion;
            }
        }

        return $unsupported;
    }

    private function find(int $version): ?MajorVersion
    {
        foreach ($this->majorVersions as $majorVersion) {
            if ($majorVersion->version() === $version) {
                return $majorVersion;
            }
        }

        return null;
    }
}
