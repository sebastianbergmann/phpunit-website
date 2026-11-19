<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_reverse;
use DateTimeImmutable;

final readonly class BackwardCompatibilityRenderer implements PageRenderer
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
            'backward-compatibility.html',
            $this->templates->renderPage(
                'backward-compatibility.html',
                [
                    'deprecations' => $this->deprecations(),
                ],
            ),
        );
    }

    /**
     * The lists of deprecations, the one of the oldest major version first.
     * Each is linked on the branch of the newest minor version.
     *
     * @throws InvalidConfigurationException
     * @throws TemplateException
     */
    private function deprecations(): string
    {
        $buffer = '';

        foreach (array_reverse($this->site->majorVersions()->released($this->today)) as $majorVersion) {
            if (!$majorVersion->hasDeprecationList()) {
                continue;
            }

            $buffer .= $this->templates->render(
                'backward-compatibility/deprecations.html',
                [
                    'version' => $majorVersion->minorVersion($this->today),
                ],
            );
        }

        return $buffer;
    }
}
