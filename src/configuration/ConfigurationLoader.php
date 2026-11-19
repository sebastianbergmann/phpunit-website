<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_key_exists;
use function array_keys;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_file;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;
use function str_starts_with;
use DateTimeImmutable;

/**
 * Turns the array that config.php returns into value objects and rejects
 * everything that is missing, misspelled, or of the wrong type, so that a
 * mistake in the configuration fails the build instead of reaching a page.
 */
final readonly class ConfigurationLoader
{
    /**
     * @param non-empty-string $file
     *
     * @throws InvalidConfigurationException
     */
    public function load(string $file): Site
    {
        if (!is_file($file)) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Configuration file %s does not exist',
                    $file,
                ),
            );
        }

        $configuration = require $file;

        if (!is_array($configuration)) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Configuration file %s does not return an array',
                    $file,
                ),
            );
        }

        $this->ensureOnlyKnownKeys($configuration, ['majorVersions', 'articles', 'sponsors'], 'configuration');

        $sponsors = $this->map($configuration, 'sponsors', 'sponsors');

        $this->ensureOnlyKnownKeys($sponsors, ['business', 'infrastructure'], 'sponsors');

        return new Site(
            $this->majorVersions($configuration),
            $this->articles($configuration),
            $this->sponsors($sponsors, 'business'),
            $this->sponsors($sponsors, 'infrastructure'),
        );
    }

    /**
     * @param array<mixed> $configuration
     *
     * @throws InvalidConfigurationException
     */
    private function majorVersions(array $configuration): MajorVersionCollection
    {
        $majorVersions = [];

        foreach ($this->listOfMaps($configuration, 'majorVersions', 'majorVersions') as $index => $data) {
            $majorVersions[] = $this->majorVersion($data, sprintf('majorVersions[%d]', $index));
        }

        if ($majorVersions === []) {
            throw new InvalidConfigurationException('majorVersions must not be empty');
        }

        return MajorVersionCollection::fromList($majorVersions);
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     */
    private function majorVersion(array $data, string $path): MajorVersion
    {
        $this->ensureOnlyKnownKeys(
            $data,
            [
                'version',
                'released',
                'minimumPhpVersion',
                'maximumPhpVersion',
                'minorVersion',
                'nextMinorVersion',
                'endOfBugfixSupport',
                'endOfLife',
                'documentation',
                'deprecations',
                'milestone',
                'tutorialPhpVersions',
            ],
            $path,
        );

        $version      = $this->positiveInteger($data, 'version', $path);
        $minorVersion = null;

        if (array_key_exists('minorVersion', $data)) {
            $minorVersion = $this->minorVersionOf($version, $data, 'minorVersion', $path);
        }

        $endOfLife = $this->optionalDate($data, 'endOfLife', $path);

        if ($minorVersion === null && $endOfLife === null) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s.minorVersion is required for a major version that has no endOfLife',
                    $path,
                ),
            );
        }

        $maximumPhpVersion = null;

        if (array_key_exists('maximumPhpVersion', $data)) {
            $maximumPhpVersion = $this->phpVersion($data, 'maximumPhpVersion', $path);
        }

        $nextMinorVersion = null;

        if (array_key_exists('nextMinorVersion', $data)) {
            $nextMinorVersion = $this->plannedMinorVersion(
                $version,
                $this->map($data, 'nextMinorVersion', $path . '.nextMinorVersion'),
                $path . '.nextMinorVersion',
            );
        }

        $documentation = null;

        if (array_key_exists('documentation', $data)) {
            $documentation = $this->string($data, 'documentation', $path);
        }

        $milestone = null;

        if (array_key_exists('milestone', $data)) {
            $milestone = $this->positiveInteger($data, 'milestone', $path);
        }

        $tutorialPhpVersions = [];

        if (array_key_exists('tutorialPhpVersions', $data)) {
            $tutorialPhpVersions = $this->phpVersions($data, 'tutorialPhpVersions', $path);
        }

        return new MajorVersion(
            $version,
            $this->date($data, 'released', $path),
            $this->phpVersion($data, 'minimumPhpVersion', $path),
            $maximumPhpVersion,
            $minorVersion,
            $nextMinorVersion,
            $this->optionalDate($data, 'endOfBugfixSupport', $path),
            $endOfLife,
            $documentation,
            $this->boolean($data, 'deprecations', $path),
            $milestone,
            $tutorialPhpVersions,
        );
    }

    /**
     * @param positive-int     $majorVersion
     * @param array<mixed>     $data
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     */
    private function plannedMinorVersion(int $majorVersion, array $data, string $path): PlannedMinorVersion
    {
        $this->ensureOnlyKnownKeys($data, ['version', 'released', 'milestone'], $path);

        return new PlannedMinorVersion(
            $this->minorVersionOf($majorVersion, $data, 'version', $path),
            $this->date($data, 'released', $path),
            $this->positiveInteger($data, 'milestone', $path),
        );
    }

    /**
     * @param array<mixed> $configuration
     *
     * @throws InvalidConfigurationException
     *
     * @return list<Article>
     */
    private function articles(array $configuration): array
    {
        $articles = [];

        foreach ($this->listOfMaps($configuration, 'articles', 'articles') as $index => $data) {
            $path = sprintf('articles[%d]', $index);

            $this->ensureOnlyKnownKeys($data, ['date', 'title', 'url'], $path);

            $articles[] = new Article(
                $this->date($data, 'date', $path),
                $this->string($data, 'title', $path),
                $this->string($data, 'url', $path),
            );
        }

        return $articles;
    }

    /**
     * @param array<mixed>     $sponsors
     * @param non-empty-string $kind
     *
     * @throws InvalidConfigurationException
     *
     * @return list<Sponsor>
     */
    private function sponsors(array $sponsors, string $kind): array
    {
        $result = [];

        foreach ($this->listOfMaps($sponsors, $kind, 'sponsors.' . $kind) as $index => $data) {
            $path = sprintf('sponsors.%s[%d]', $kind, $index);

            $this->ensureOnlyKnownKeys($data, ['name', 'url', 'logo'], $path);

            $result[] = new Sponsor(
                $this->string($data, 'name', $path),
                $this->string($data, 'url', $path),
                $this->string($data, 'logo', $path),
            );
        }

        return $result;
    }

    /**
     * @param array<mixed>           $data
     * @param list<non-empty-string> $knownKeys
     * @param non-empty-string       $path
     *
     * @throws InvalidConfigurationException
     */
    private function ensureOnlyKnownKeys(array $data, array $knownKeys, string $path): void
    {
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $knownKeys, true)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        '%s has the unknown key "%s" (known keys: %s)',
                        $path,
                        $key,
                        implode(', ', $knownKeys),
                    ),
                );
            }
        }
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     *
     * @return array<mixed>
     */
    private function map(array $data, string $key, string $path): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s must be an array',
                    $path,
                ),
            );
        }

        return $data[$key];
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     *
     * @return list<array<mixed>>
     */
    private function listOfMaps(array $data, string $key, string $path): array
    {
        $result = [];

        foreach ($this->map($data, $key, $path) as $element) {
            if (!is_array($element)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        '%s must be a list of arrays',
                        $path,
                    ),
                );
            }

            $result[] = $element;
        }

        return $result;
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     *
     * @return non-empty-string
     */
    private function string(array $data, string $key, string $path): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key]) || $data[$key] === '') {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s.%s must be a non-empty string',
                    $path,
                    $key,
                ),
            );
        }

        return $data[$key];
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     *
     * @return positive-int
     */
    private function positiveInteger(array $data, string $key, string $path): int
    {
        if (!array_key_exists($key, $data) || !is_int($data[$key]) || $data[$key] < 1) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s.%s must be a positive integer',
                    $path,
                    $key,
                ),
            );
        }

        return $data[$key];
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     */
    private function boolean(array $data, string $key, string $path): bool
    {
        if (!array_key_exists($key, $data)) {
            return false;
        }

        if (!is_bool($data[$key])) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s.%s must be true or false',
                    $path,
                    $key,
                ),
            );
        }

        return $data[$key];
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     */
    private function date(array $data, string $key, string $path): DateTimeImmutable
    {
        $value = $this->string($data, $key, $path);
        $date  = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s.%s must be a date in the format YYYY-MM-DD, "%s" is not',
                    $path,
                    $key,
                    $value,
                ),
            );
        }

        return $date;
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     */
    private function optionalDate(array $data, string $key, string $path): ?DateTimeImmutable
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        return $this->date($data, $key, $path);
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     *
     * @return non-empty-string
     */
    private function phpVersion(array $data, string $key, string $path): string
    {
        $value = $this->string($data, $key, $path);

        $this->ensureIsMinorVersionNumber($value, $path . '.' . $key);

        return $value;
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     *
     * @return list<non-empty-string>
     */
    private function phpVersions(array $data, string $key, string $path): array
    {
        $result = [];

        foreach ($this->map($data, $key, $path . '.' . $key) as $value) {
            if (!is_string($value) || $value === '') {
                throw new InvalidConfigurationException(
                    sprintf(
                        '%s.%s must be a list of PHP versions such as "8.4"',
                        $path,
                        $key,
                    ),
                );
            }

            $this->ensureIsMinorVersionNumber($value, $path . '.' . $key);

            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param positive-int     $majorVersion
     * @param array<mixed>     $data
     * @param non-empty-string $key
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     *
     * @return non-empty-string
     */
    private function minorVersionOf(int $majorVersion, array $data, string $key, string $path): string
    {
        $value = $this->string($data, $key, $path);

        $this->ensureIsMinorVersionNumber($value, $path . '.' . $key);

        if (!str_starts_with($value, $majorVersion . '.')) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s.%s must be a minor version of PHPUnit %d, "%s" is not',
                    $path,
                    $key,
                    $majorVersion,
                    $value,
                ),
            );
        }

        return $value;
    }

    /**
     * @param non-empty-string $value
     * @param non-empty-string $path
     *
     * @throws InvalidConfigurationException
     */
    private function ensureIsMinorVersionNumber(string $value, string $path): void
    {
        if (preg_match('/^\d+\.\d+$/', $value) !== 1) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s must be a version number such as "8.4", "%s" is not',
                    $path,
                    $value,
                ),
            );
        }
    }
}
