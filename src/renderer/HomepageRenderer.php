<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_slice;
use function sprintf;
use function usort;
use DateTimeImmutable;

final readonly class HomepageRenderer implements PageRenderer
{
    use Formatting;

    private const int NUMBER_OF_ANNOUNCEMENTS = 3;
    private const int NUMBER_OF_ARTICLES      = 3;
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
        $current = $this->site->majorVersions()->current($this->today);

        $this->publicDirectory->write(
            'index.html',
            $this->templates->renderPage(
                'index.html',
                [
                    'majorVersion'      => (string) $current->version(),
                    'minimumPhpVersion' => $current->minimumPhpVersion(),
                    'currentVersion'    => $this->currentVersion($current),
                    'nextMinorVersion'  => $this->nextMinorVersion($current),
                    'nextMajorVersion'  => $this->nextMajorVersion(),
                    'announcements'     => $this->announcements(),
                    'articles'          => $this->articles(),
                ],
            ),
        );
    }

    private function currentVersion(MajorVersion $current): string
    {
        $url = $this->publicDirectory->announcementUrl($current);

        if ($url === null) {
            return $current->name();
        }

        return sprintf('<a href="%s">%s</a>', $url, $current->name());
    }

    /**
     * @throws InvalidConfigurationException
     * @throws TemplateException
     */
    private function nextMinorVersion(MajorVersion $current): string
    {
        $next = $current->nextMinorVersion($this->today);

        if ($next === null) {
            return '';
        }

        $milestone = $next->milestone();

        if ($milestone === null) {
            throw new InvalidConfigurationException(
                sprintf(
                    'PHPUnit %s is not released on %s and needs a milestone',
                    $next->version(),
                    $this->isoDate($this->today),
                ),
            );
        }

        return $this->templates->render(
            'homepage/next-version.html',
            [
                'milestone' => (string) $milestone,
                'version'   => $next->version(),
                'kind'      => 'minor',
            ],
        );
    }

    /**
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

        return $this->templates->render(
            'homepage/next-version.html',
            [
                'milestone' => (string) $milestone,
                'version'   => (string) $next->version(),
                'kind'      => 'major',
            ],
        );
    }

    /**
     * @throws TemplateException
     */
    private function announcements(): string
    {
        $buffer = '';
        $number = 0;

        foreach ($this->site->majorVersions()->released($this->today) as $majorVersion) {
            $url = $this->publicDirectory->announcementUrl($majorVersion);

            if ($url === null) {
                continue;
            }

            $buffer .= $this->templates->render(
                'homepage/announcement.html',
                [
                    'isoDate' => $this->isoDate($majorVersion->released()),
                    'date'    => $this->formatDate($majorVersion->released()),
                    'url'     => $url,
                    'version' => (string) $majorVersion->version(),
                ],
            );

            $number++;

            if ($number === self::NUMBER_OF_ANNOUNCEMENTS) {
                break;
            }
        }

        return $buffer;
    }

    /**
     * @throws TemplateException
     */
    private function articles(): string
    {
        $articles = [];

        foreach ($this->site->articles() as $article) {
            if ($article->isPublished($this->today)) {
                $articles[] = $article;
            }
        }

        usort(
            $articles,
            static fn (Article $a, Article $b): int => $b->date() <=> $a->date(),
        );

        $buffer = '';

        foreach (array_slice($articles, 0, self::NUMBER_OF_ARTICLES) as $article) {
            $buffer .= $this->templates->render(
                'homepage/article.html',
                [
                    'isoDate' => $this->isoDate($article->date()),
                    'date'    => $this->formatDate($article->date()),
                    'url'     => $this->escape($article->url()),
                    'title'   => $this->escape($article->title()),
                ],
            );
        }

        return $buffer;
    }
}
