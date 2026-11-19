<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use const PHP_EOL;
use function implode;
use function sprintf;
use function str_replace;
use function usort;
use function version_compare;
use DateTimeImmutable;

/**
 * Renders the index of the announcements: the releases of major versions
 * that have a page in public/announcements/, the releases of minor versions,
 * and the announcements that are configured as such. The newest one comes
 * first; of two releases on the same day, the one of the newer version does.
 */
final readonly class AnnouncementsRenderer implements PageRenderer
{
    use Formatting;

    /**
     * The release of a minor version is announced with a release on GitHub
     * as of this major version, and with a section of a ChangeLog before it.
     */
    private const int FIRST_MAJOR_VERSION_WITH_RELEASES_ON_GITHUB = 11;
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
        $entries = [];

        foreach ($this->site->majorVersions()->released($this->today) as $majorVersion) {
            $url = $this->publicDirectory->announcementUrl($majorVersion);

            if ($url !== null) {
                $entries[] = [
                    'date'    => $majorVersion->released(),
                    'version' => $majorVersion->version() . '.0',
                    'html'    => $this->majorVersion($majorVersion, $url),
                ];
            }

            foreach ($majorVersion->releasedMinorVersions($this->today) as $minorVersion) {
                $entries[] = [
                    'date'    => $minorVersion->released(),
                    'version' => $minorVersion->version(),
                    'html'    => $this->minorVersion($majorVersion, $minorVersion),
                ];
            }
        }

        foreach ($this->site->announcements() as $announcement) {
            if (!$announcement->isPublished($this->today)) {
                continue;
            }

            $entries[] = [
                'date'    => $announcement->date(),
                'version' => '0.0',
                'html'    => $this->announcement($announcement),
            ];
        }

        usort(
            $entries,
            static function (array $a, array $b): int
            {
                $byDate = $b['date'] <=> $a['date'];

                if ($byDate !== 0) {
                    return $byDate;
                }

                return version_compare($b['version'], $a['version']);
            },
        );

        $html = [];

        foreach ($entries as $entry) {
            $html[] = $entry['html'];
        }

        $this->publicDirectory->write(
            'announcements/index.html',
            $this->templates->renderPage(
                'announcements/index.html',
                [
                    'announcements' => implode(PHP_EOL, $html),
                ],
            ),
        );
    }

    /**
     * @param non-empty-string $url
     *
     * @throws TemplateException
     */
    private function majorVersion(MajorVersion $majorVersion, string $url): string
    {
        return $this->templates->render(
            'announcements/major-version.html',
            [
                'url'     => $url,
                'version' => (string) $majorVersion->version(),
                'isoDate' => $this->isoDate($majorVersion->released()),
                'date'    => $this->formatDate($majorVersion->released()),
            ],
        );
    }

    /**
     * @throws TemplateException
     */
    private function minorVersion(MajorVersion $majorVersion, MinorVersion $minorVersion): string
    {
        return $this->templates->render(
            'announcements/minor-version.html',
            [
                'url'     => $this->escape($this->urlOfMinorVersion($majorVersion, $minorVersion)),
                'version' => $minorVersion->version(),
                'isoDate' => $this->isoDate($minorVersion->released()),
                'date'    => $this->formatDate($minorVersion->released()),
            ],
        );
    }

    /**
     * @return non-empty-string
     */
    private function urlOfMinorVersion(MajorVersion $majorVersion, MinorVersion $minorVersion): string
    {
        $configured = $minorVersion->configuredAnnouncementUrl();

        if ($configured !== null) {
            return $configured;
        }

        if ($majorVersion->version() >= self::FIRST_MAJOR_VERSION_WITH_RELEASES_ON_GITHUB) {
            return sprintf(
                'https://github.com/sebastianbergmann/phpunit/releases/tag/%s.0',
                $minorVersion->version(),
            );
        }

        return sprintf(
            'https://github.com/sebastianbergmann/phpunit/blob/%1$s.0/ChangeLog-%1$s.md#%2$s0---%3$s',
            $minorVersion->version(),
            str_replace('.', '', $minorVersion->version()),
            $this->isoDate($minorVersion->released()),
        );
    }

    /**
     * @throws InvalidConfigurationException
     * @throws TemplateException
     */
    private function announcement(Announcement $announcement): string
    {
        if (!$this->publicDirectory->has('announcements/' . $announcement->page())) {
            throw new InvalidConfigurationException(
                sprintf(
                    'The page %s of the announcement "%s" does not exist in public/announcements/',
                    $announcement->page(),
                    $announcement->title(),
                ),
            );
        }

        return $this->templates->render(
            'announcements/announcement.html',
            [
                'url'     => '/announcements/' . $this->escape($announcement->page()),
                'title'   => $this->escape($announcement->title()),
                'isoDate' => $this->isoDate($announcement->date()),
                'date'    => $this->formatDate($announcement->date()),
                'text'    => $this->escape($announcement->text()),
            ],
        );
    }
}
