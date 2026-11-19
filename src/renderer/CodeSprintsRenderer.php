<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function array_reverse;
use function sprintf;
use function usort;
use DateTimeImmutable;

/**
 * Renders the index of the code sprints and one page for each of them.
 *
 * A code sprint that is not over on the day of the build is the next event
 * of the index. The photos and quotes of a code sprint are the template
 * templates/code-sprints/impressions/<slug>.html, which does not have to
 * exist.
 */
final readonly class CodeSprintsRenderer implements PageRenderer
{
    use Formatting;

    private const string WIDTH_OF_ORGANIZER_LOGO = '200';
    private const string WIDTH_OF_SUPPORTER_LOGO = '100';
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
        $codeSprints = $this->site->codeSprints();

        usort(
            $codeSprints,
            static fn (CodeSprint $a, CodeSprint $b): int => $b->start() <=> $a->start(),
        );

        $this->publicDirectory->write(
            'code-sprints/index.html',
            $this->templates->renderPage(
                'code-sprints/index.html',
                [
                    'nextEvent'  => $this->nextEvent($codeSprints),
                    'pastEvents' => $this->pastEvents($codeSprints),
                ],
            ),
        );

        foreach ($codeSprints as $codeSprint) {
            $this->publicDirectory->write(
                sprintf('code-sprints/%s.html', $codeSprint->slug()),
                $this->templates->renderPage(
                    'code-sprints/code-sprint.html',
                    [
                        'title'       => $this->escape($codeSprint->title()),
                        'description' => $this->escape($this->description($codeSprint)),
                        'slug'        => $codeSprint->slug(),
                        'location'    => $this->escape($codeSprint->location()),
                        'isoStart'    => $this->isoDate($codeSprint->start()),
                        'isoEnd'      => $this->isoDate($codeSprint->end()),
                        'dates'       => $codeSprint->dates(),
                        'impressions' => $this->impressions($codeSprint),
                        'organizers'  => $this->organizations($codeSprint->organizers(), self::WIDTH_OF_ORGANIZER_LOGO),
                        'supporters'  => $this->organizations($codeSprint->supporters(), self::WIDTH_OF_SUPPORTER_LOGO),
                    ],
                ),
            );
        }
    }

    /**
     * @param list<CodeSprint> $codeSprints the newest one first
     *
     * @throws TemplateException
     */
    private function nextEvent(array $codeSprints): string
    {
        foreach (array_reverse($codeSprints) as $codeSprint) {
            if ($codeSprint->isOver($this->today)) {
                continue;
            }

            return $this->templates->render(
                'code-sprints/next-event.html',
                [
                    'slug'     => $codeSprint->slug(),
                    'dates'    => $codeSprint->dates(),
                    'location' => $this->escape($codeSprint->location()),
                ],
            );
        }

        return $this->templates->render('code-sprints/no-next-event.html', []);
    }

    /**
     * @param list<CodeSprint> $codeSprints the newest one first
     *
     * @throws TemplateException
     */
    private function pastEvents(array $codeSprints): string
    {
        $buffer = '';

        foreach ($codeSprints as $codeSprint) {
            if (!$codeSprint->isOver($this->today)) {
                continue;
            }

            $buffer .= $this->templates->render(
                'code-sprints/past-event.html',
                [
                    'slug'  => $codeSprint->slug(),
                    'dates' => $codeSprint->dates(),
                ],
            );
        }

        return $buffer;
    }

    /**
     * @return non-empty-string
     */
    private function description(CodeSprint $codeSprint): string
    {
        $verb = 'will be';

        if ($codeSprint->isOver($this->today)) {
            $verb = 'was';
        }

        return sprintf(
            'On %s, there %s a code sprint in %s to work on PHPUnit and onboard new developers to the project.',
            $codeSprint->dates(),
            $verb,
            $codeSprint->location(),
        );
    }

    /**
     * @throws TemplateException
     */
    private function impressions(CodeSprint $codeSprint): string
    {
        $template = sprintf('code-sprints/impressions/%s.html', $codeSprint->slug());

        if (!$this->templates->has($template)) {
            return '';
        }

        return $this->templates->render($template, []);
    }

    /**
     * @param list<Sponsor>    $organizations
     * @param non-empty-string $width
     *
     * @throws InvalidConfigurationException
     * @throws TemplateException
     */
    private function organizations(array $organizations, string $width): string
    {
        $buffer = '';

        foreach ($organizations as $organization) {
            if (!$this->publicDirectory->has('img/sponsors/' . $organization->logo())) {
                throw new InvalidConfigurationException(
                    sprintf(
                        'The logo %s of %s does not exist in public/img/sponsors/',
                        $organization->logo(),
                        $organization->name(),
                    ),
                );
            }

            $buffer .= $this->templates->render(
                'code-sprints/organization.html',
                [
                    'url'   => $this->escape($organization->url()),
                    'logo'  => $this->escape($organization->logo()),
                    'name'  => $this->escape($organization->name()),
                    'width' => $width,
                ],
            );
        }

        return $buffer;
    }
}
