<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

/**
 * An announcement that is not the release of a version.
 */
final readonly class Announcement
{
    private DateTimeImmutable $date;

    /**
     * @var non-empty-string
     */
    private string $title;

    /**
     * @var non-empty-string
     */
    private string $page;

    /**
     * @var non-empty-string
     */
    private string $text;

    /**
     * @param non-empty-string $title
     * @param non-empty-string $page
     * @param non-empty-string $text
     */
    public function __construct(DateTimeImmutable $date, string $title, string $page, string $text)
    {
        $this->date  = $date;
        $this->title = $title;
        $this->page  = $page;
        $this->text  = $text;
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }

    public function isPublished(DateTimeImmutable $today): bool
    {
        return $this->date <= $today;
    }

    /**
     * @return non-empty-string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * The name of the file in public/announcements/.
     *
     * @return non-empty-string
     */
    public function page(): string
    {
        return $this->page;
    }

    /**
     * @return non-empty-string
     */
    public function text(): string
    {
        return $this->text;
    }
}
