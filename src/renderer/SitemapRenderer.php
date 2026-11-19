<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use const SORT_STRING;
use function ksort;
use function str_ends_with;
use function str_starts_with;
use function substr;
use DateTimeImmutable;

/**
 * Lists every HTML page of the document root that the configuration does not
 * exclude, with the day it was last changed.
 *
 * It has to be the last renderer that runs: a page that another renderer
 * has just changed was last changed today.
 */
final readonly class SitemapRenderer implements PageRenderer
{
    use Formatting;

    private const string BASE_URL = 'https://phpunit.de/';
    private Site $site;
    private DateTimeImmutable $today;
    private TemplateRenderer $templates;
    private PublicDirectory $publicDirectory;
    private PageHistory $pageHistory;

    public function __construct(Site $site, DateTimeImmutable $today, TemplateRenderer $templates, PublicDirectory $publicDirectory, PageHistory $pageHistory)
    {
        $this->site            = $site;
        $this->today           = $today;
        $this->templates       = $templates;
        $this->publicDirectory = $publicDirectory;
        $this->pageHistory     = $pageHistory;
    }

    public function render(): void
    {
        $urls = [];

        foreach ($this->publicDirectory->htmlPages() as $page) {
            if ($this->isExcluded($page)) {
                continue;
            }

            $urls[$this->url($page)] = $page;
        }

        ksort($urls, SORT_STRING);

        $buffer = '';

        foreach ($urls as $url => $page) {
            $buffer .= $this->templates->render(
                'sitemap/url.xml',
                [
                    'url'          => $this->escape(self::BASE_URL . $url),
                    'lastModified' => $this->isoDate($this->pageHistory->lastModified($page, $this->today)),
                ],
            );
        }

        $this->publicDirectory->write(
            'sitemap.xml',
            $this->templates->render(
                'sitemap/sitemap.xml',
                [
                    'urls' => $buffer,
                ],
            ),
        );
    }

    /**
     * An exclusion that ends with a slash excludes a directory, every other
     * one excludes a single page.
     *
     * @param non-empty-string $page
     */
    private function isExcluded(string $page): bool
    {
        foreach ($this->site->pagesExcludedFromSitemap() as $excluded) {
            if ($page === $excluded) {
                return true;
            }

            if (str_ends_with($excluded, '/') && str_starts_with($page, $excluded)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The index of a directory is addressed by the directory.
     *
     * @param non-empty-string $page
     */
    private function url(string $page): string
    {
        if ($page === 'index.html') {
            return '';
        }

        if (str_ends_with($page, '/index.html')) {
            return substr($page, 0, -10);
        }

        return $page;
    }
}
