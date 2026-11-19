<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;

final readonly class DocumentationRenderer implements PageRenderer
{
    use Formatting;
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
            'documentation.html',
            $this->templates->renderPage(
                'documentation.html',
                [
                    'supportedVersions'   => $this->supportedVersions(),
                    'unsupportedVersions' => $this->unsupportedVersions(),
                ],
            ),
        );
    }

    /**
     * @throws InvalidConfigurationException
     * @throws TemplateException
     */
    private function supportedVersions(): string
    {
        $buffer = '';

        foreach ($this->site->majorVersions()->supported($this->today) as $majorVersion) {
            $url = $majorVersion->documentationUrl($this->today);

            if ($url === null) {
                continue;
            }

            $buffer .= $this->templates->render(
                'documentation/supported.html',
                [
                    'url'     => $this->escape($url),
                    'version' => $majorVersion->minorVersion($this->today),
                ],
            );
        }

        return $buffer;
    }

    /**
     * @throws TemplateException
     */
    private function unsupportedVersions(): string
    {
        $buffer = '';

        foreach ($this->site->majorVersions()->unsupported($this->today) as $majorVersion) {
            $url = $majorVersion->documentationUrl($this->today);

            if ($url === null) {
                continue;
            }

            $buffer .= $this->templates->render(
                'documentation/unsupported.html',
                [
                    'url' => $this->escape($url),
                ],
            );
        }

        return $buffer;
    }
}
