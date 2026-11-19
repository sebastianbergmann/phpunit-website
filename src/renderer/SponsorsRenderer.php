<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function sprintf;

final readonly class SponsorsRenderer implements PageRenderer
{
    use Formatting;
    private Site $site;
    private TemplateRenderer $templates;
    private PublicDirectory $publicDirectory;

    public function __construct(Site $site, TemplateRenderer $templates, PublicDirectory $publicDirectory)
    {
        $this->site            = $site;
        $this->templates       = $templates;
        $this->publicDirectory = $publicDirectory;
    }

    public function render(): void
    {
        $this->publicDirectory->write(
            'sponsors.html',
            $this->templates->renderPage(
                'sponsors.html',
                [
                    'businessSponsors'       => $this->sponsors($this->site->businessSponsors()),
                    'infrastructureSponsors' => $this->sponsors($this->site->infrastructureSponsors()),
                ],
            ),
        );
    }

    /**
     * @param list<Sponsor> $sponsors
     *
     * @throws InvalidConfigurationException
     * @throws TemplateException
     */
    private function sponsors(array $sponsors): string
    {
        $buffer = '';

        foreach ($sponsors as $sponsor) {
            if (!$this->publicDirectory->has('img/sponsors/' . $sponsor->logo())) {
                throw new InvalidConfigurationException(
                    sprintf(
                        'The logo %s of sponsor %s does not exist in public/img/sponsors/',
                        $sponsor->logo(),
                        $sponsor->name(),
                    ),
                );
            }

            $buffer .= $this->templates->render(
                'sponsors/sponsor.html',
                [
                    'url'  => $this->escape($sponsor->url()),
                    'logo' => $this->escape($sponsor->logo()),
                    'name' => $this->escape($sponsor->name()),
                ],
            );
        }

        return $buffer;
    }
}
