<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use const PHP_EOL;
use function sprintf;
use DateTimeImmutable;

final readonly class SupportedVersionsRenderer implements PageRenderer
{
    use Formatting;
    private Site $site;
    private DateTimeImmutable $today;
    private TemplateRenderer $templates;
    private PublicDirectory $publicDirectory;

    public function __construct(Site $site, DateTimeImmutable $today, TemplateRenderer $templates, PublicDirectory $publicDirectory)
    {
        $this->site            = $site;
        $this->today           = $today;
        $this->templates       = $templates;
        $this->publicDirectory = $publicDirectory;
    }

    public function render(): void
    {
        $majorVersions = $this->site->majorVersions();

        $this->publicDirectory->write(
            'supported-versions.html',
            $this->templates->renderPage(
                'supported-versions.html',
                [
                    'supportedVersions'   => $this->rows($majorVersions->supported($this->today)),
                    'unsupportedVersions' => $this->rows($majorVersions->unsupported($this->today)),
                    'nextMajorVersion'    => $this->nextMajorVersion(),
                ],
            ),
        );
    }

    /**
     * @param list<MajorVersion> $majorVersions
     *
     * @throws InvalidConfigurationException
     * @throws TemplateException
     */
    private function rows(array $majorVersions): string
    {
        $buffer = '';

        foreach ($majorVersions as $majorVersion) {
            $status             = $this->site->majorVersions()->status($majorVersion, $this->today);
            $endOfBugfixSupport = $this->site->majorVersions()->endOfBugfixSupport($majorVersion);

            $buffer .= $this->templates->render(
                'supported-versions/row.html',
                [
                    'rowClass'              => $this->rowClass($status),
                    'name'                  => $this->name($majorVersion),
                    'badgeClass'            => $this->badgeClass($status),
                    'status'                => $this->label($status),
                    'supportedMinorVersion' => $this->supportedMinorVersion($majorVersion, $status),
                    'phpCompatibility'      => $this->escape($majorVersion->phpCompatibility()),
                    'releasedIso'           => $this->isoDate($majorVersion->released()),
                    'released'              => $this->formatDate($majorVersion->released()),
                    'endOfBugfixSupportIso' => $this->isoDate($endOfBugfixSupport),
                    'endOfBugfixSupport'    => $this->formatDate($endOfBugfixSupport),
                    'endOfLife'             => $this->endOfLife($majorVersion),
                ],
            );
        }

        return $buffer;
    }

    private function name(MajorVersion $majorVersion): string
    {
        $url = $this->publicDirectory->announcementUrl($majorVersion);

        if ($url === null) {
            return $majorVersion->name();
        }

        return sprintf('<a href="%s">%s</a>', $url, $majorVersion->name());
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function supportedMinorVersion(MajorVersion $majorVersion, SupportStatus $status): string
    {
        if ($status === SupportStatus::EndOfLife) {
            return 'None';
        }

        return $majorVersion->minorVersion($this->today);
    }

    private function endOfLife(MajorVersion $majorVersion): string
    {
        $endOfLife = $majorVersion->endOfLife();

        if ($endOfLife === null) {
            return 'To be determined';
        }

        return sprintf(
            '<time datetime="%s">%s</time>',
            $this->isoDate($endOfLife),
            $this->formatDate($endOfLife),
        );
    }

    /**
     * The note about the major version that is released next. Bugfix support
     * for the major version before the current one ends on the day of that
     * release.
     *
     * @throws InvalidConfigurationException
     * @throws TemplateException
     */
    private function nextMajorVersion(): string
    {
        $next = $this->site->majorVersions()->next($this->today);

        if ($next === null) {
            return '';
        }

        $milestone = $next->milestone();

        if ($milestone === null) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s is not released on %s and needs a milestone',
                    $next->name(),
                    $this->isoDate($this->today),
                ),
            );
        }

        return PHP_EOL . $this->templates->render(
            'supported-versions/next-major-version.html',
            [
                'milestone'                  => (string) $milestone,
                'version'                    => (string) $next->version(),
                'isoDate'                    => $this->isoDate($next->released()),
                'date'                       => $this->formatDate($next->released()),
                'versionLosingBugfixSupport' => (string) $this->site->majorVersions()->get($next->version() - 2)->version(),
            ],
        );
    }

    /**
     * @return non-empty-string
     */
    private function label(SupportStatus $status): string
    {
        return match ($status) {
            SupportStatus::BugfixSupport => 'Bugfix Support',
            SupportStatus::LifeSupport   => 'Life Support',
            SupportStatus::EndOfLife     => 'End of Life',
        };
    }

    /**
     * @return non-empty-string
     */
    private function rowClass(SupportStatus $status): string
    {
        return match ($status) {
            SupportStatus::BugfixSupport => 'is-supported',
            SupportStatus::LifeSupport   => 'is-security',
            SupportStatus::EndOfLife     => 'is-eol',
        };
    }

    /**
     * @return non-empty-string
     */
    private function badgeClass(SupportStatus $status): string
    {
        return match ($status) {
            SupportStatus::BugfixSupport => 'badge--ok',
            SupportStatus::LifeSupport   => 'badge--warn',
            SupportStatus::EndOfLife     => 'badge--danger',
        };
    }
}
