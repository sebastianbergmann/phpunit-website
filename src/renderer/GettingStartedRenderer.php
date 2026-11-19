<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

/**
 * The tutorial is written for the current major version: its name, the name
 * of its PHAR, the oldest version of PHP it runs on, and its newest minor
 * version in the sample output all follow from the configuration.
 */
final readonly class GettingStartedRenderer implements PageRenderer
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
        $current = $this->site->majorVersions()->current($this->today);

        $this->publicDirectory->write(
            'getting-started.html',
            $this->templates->renderPage(
                'getting-started.html',
                [
                    'minimumPhpVersion' => $current->minimumPhpVersion(),
                    'minorVersion'      => $current->minorVersion($this->today),
                    'majorVersion'      => (string) $current->version(),
                ],
            ),
        );
    }
}
