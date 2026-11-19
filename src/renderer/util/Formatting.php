<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function htmlspecialchars;
use DateTimeImmutable;

trait Formatting
{
    /**
     * @return non-empty-string
     */
    private function formatDate(DateTimeImmutable $date): string
    {
        return $date->format('F j, Y');
    }

    /**
     * @return non-empty-string
     */
    private function isoDate(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d');
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text);
    }
}
