<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

interface PageHistory
{
    /**
     * The day a file in the document root was last changed.
     *
     * @param non-empty-string $path relative to the document root
     *
     * @throws PageHistoryException
     */
    public function lastModified(string $path, DateTimeImmutable $today): DateTimeImmutable;
}
