<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function sprintf;
use DateTimeImmutable;

/**
 * Renders one page for the release of each major version and for each other
 * announcement, as long as its text exists: the template
 * templates/announcements/content/<page>. Title, description, and date of
 * the page follow from the configuration.
 *
 * It has to run before the renderers that link to an announcement: they
 * only do so when its page exists in the document root.
 */
final readonly class AnnouncementPagesRenderer implements PageRenderer
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
        foreach ($this->site->majorVersions()->released($this->today) as $majorVersion) {
            $this->renderPage(
                sprintf('phpunit-%d.html', $majorVersion->version()),
                sprintf('Release Announcement for Version %d of PHPUnit', $majorVersion->version()),
                sprintf(
                    'The PHPUnit development team is pleased to announce the immediate availability of %s. This release adds new features, changes and removes existing features, and fixes bugs.',
                    $majorVersion->name(),
                ),
                $majorVersion->released(),
            );
        }

        foreach ($this->site->announcements() as $announcement) {
            if (!$announcement->isPublished($this->today)) {
                continue;
            }

            $this->renderPage(
                $announcement->page(),
                sprintf('%s PHPUnit', $announcement->title()),
                $announcement->text(),
                $announcement->date(),
            );
        }
    }

    /**
     * @param non-empty-string $page
     * @param non-empty-string $title
     * @param non-empty-string $description
     *
     * @throws TemplateException
     */
    private function renderPage(string $page, string $title, string $description, DateTimeImmutable $date): void
    {
        $content = 'announcements/content/' . $page;

        if (!$this->templates->has($content)) {
            return;
        }

        $this->publicDirectory->write(
            'announcements/' . $page,
            $this->templates->renderPage(
                'announcements/announcement.html',
                [
                    'title'       => $this->escape($title),
                    'description' => $this->escape($description),
                    'page'        => $this->escape($page),
                    'isoDate'     => $this->isoDate($date),
                    'content'     => $this->templates->render($content, []),
                ],
            ),
        );
    }
}
