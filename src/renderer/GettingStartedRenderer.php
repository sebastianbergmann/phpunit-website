<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_pop;
use function count;
use function implode;
use function sprintf;
use DateTimeImmutable;

/**
 * The tutorial is written for the current major version: its name, the name
 * of its PHAR, and its newest minor version in the sample output all follow
 * from the configuration.
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
                    'phpVersions'  => $this->phpVersions($current),
                    'minorVersion' => $current->minorVersion($this->today),
                    'majorVersion' => (string) $current->version(),
                ],
            ),
        );
    }

    /**
     * "PHP 8.4", "PHP 8.4 or PHP 8.5", or "PHP 8.4, PHP 8.5, or PHP 8.6".
     *
     * @throws InvalidConfigurationException
     */
    private function phpVersions(MajorVersion $current): string
    {
        $versions = [];

        foreach ($current->tutorialPhpVersions() as $version) {
            $versions[] = 'PHP ' . $version;
        }

        if ($versions === []) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s is the current major version and needs tutorialPhpVersions',
                    $current->name(),
                ),
            );
        }

        if (count($versions) === 1) {
            return $versions[0];
        }

        if (count($versions) === 2) {
            return $versions[0] . ' or ' . $versions[1];
        }

        $last = array_pop($versions);

        return implode(', ', $versions) . ', or ' . $last;
    }
}
