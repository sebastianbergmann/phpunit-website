<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

/**
 * The composition root: loads the configuration and wires up one renderer
 * per page.
 *
 * Everything is rendered for a given day. What counts as released, as
 * supported, or as published is decided by comparing the configured dates
 * with that day.
 */
final readonly class Factory
{
    private Site $site;
    private DateTimeImmutable $today;
    private TemplateRenderer $templates;
    private PublicDirectory $publicDirectory;
    private PageHistory $pageHistory;

    /**
     * @param non-empty-string $configurationFile
     * @param non-empty-string $templateDirectory
     * @param non-empty-string $publicDirectory
     *
     * @throws InvalidConfigurationException
     */
    public function __construct(DateTimeImmutable $today, string $configurationFile, string $templateDirectory, string $publicDirectory, PageHistory $pageHistory)
    {
        $this->site            = new ConfigurationLoader()->load($configurationFile);
        $this->today           = $today;
        $this->templates       = new TemplateRenderer($templateDirectory);
        $this->publicDirectory = new PublicDirectory($publicDirectory);
        $this->pageHistory     = $pageHistory;
    }

    public function renderer(): Renderer
    {
        return new Renderer(
            [
                new HomepageRenderer($this->site, $this->today, $this->templates, $this->publicDirectory),
                new AnnouncementsRenderer($this->site, $this->today, $this->templates, $this->publicDirectory),
                new SupportedVersionsRenderer($this->site, $this->today, $this->templates, $this->publicDirectory),
                new DocumentationRenderer($this->site, $this->today, $this->templates, $this->publicDirectory),
                new BackwardCompatibilityRenderer($this->site, $this->today, $this->templates, $this->publicDirectory),
                new GettingStartedRenderer($this->site, $this->today, $this->templates, $this->publicDirectory),
                new VerifyRenderer($this->site, $this->today, $this->templates, $this->publicDirectory),
                new SponsorsRenderer($this->site, $this->templates, $this->publicDirectory),
                new StaticPageRenderer($this->templates, $this->publicDirectory),
                new SitemapRenderer($this->site, $this->today, $this->templates, $this->publicDirectory, $this->pageHistory),
            ],
        );
    }
}
