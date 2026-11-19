<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

/**
 * Renders the pages whose content is fixed.
 *
 * Their templates are their content. They are rendered so that they share
 * the header and the footer with every other page.
 */
final readonly class StaticPageRenderer implements PageRenderer
{
    /**
     * @var non-empty-list<non-empty-string>
     */
    private const array PAGES = [
        'datenschutz.html',
        'impressum.html',
        'newsletter.html',
        'sponsoring.html',
    ];
    private TemplateRenderer $templates;
    private PublicDirectory $publicDirectory;

    public function __construct(TemplateRenderer $templates, PublicDirectory $publicDirectory)
    {
        $this->templates       = $templates;
        $this->publicDirectory = $publicDirectory;
    }

    public function render(): void
    {
        foreach (self::PAGES as $page) {
            $this->publicDirectory->write(
                $page,
                $this->templates->renderPage($page, []),
            );
        }
    }
}
