<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

final readonly class Renderer
{
    /**
     * @var list<PageRenderer>
     */
    private array $pageRenderers;

    /**
     * @param list<PageRenderer> $pageRenderers
     */
    public function __construct(array $pageRenderers)
    {
        $this->pageRenderers = $pageRenderers;
    }

    /**
     * @throws Exception
     */
    public function render(): void
    {
        foreach ($this->pageRenderers as $pageRenderer) {
            $pageRenderer->render();
        }
    }
}
