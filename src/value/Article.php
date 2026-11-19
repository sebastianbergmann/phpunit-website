<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

final readonly class Article
{
    private DateTimeImmutable $date;

    /**
     * @var non-empty-string
     */
    private string $title;

    /**
     * @var non-empty-string
     */
    private string $url;

    /**
     * @param non-empty-string $title
     * @param non-empty-string $url
     */
    public function __construct(DateTimeImmutable $date, string $title, string $url)
    {
        $this->date  = $date;
        $this->title = $title;
        $this->url   = $url;
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
     * @return non-empty-string
     */
    public function url(): string
    {
        return $this->url;
    }
}
