<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

interface PageRenderer
{
    /**
     * @throws Exception
     */
    public function render(): void;
}
