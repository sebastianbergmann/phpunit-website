<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

/**
 * The page uses the current major version as its example. The captured
 * output it shows is part of the template and is not derived.
 */
final readonly class VerifyRenderer implements PageRenderer
{
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
        $this->publicDirectory->write(
            'verify.html',
            $this->templates->renderPage(
                'verify.html',
                [
                    'majorVersion' => (string) $this->site->majorVersions()->current($this->today)->version(),
                ],
            ),
        );
    }
}
