<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

final readonly class Sponsor
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var non-empty-string
     */
    private string $url;

    /**
     * @var non-empty-string
     */
    private string $logo;

    /**
     * @param non-empty-string $name
     * @param non-empty-string $url
     * @param non-empty-string $logo
     */
    public function __construct(string $name, string $url, string $logo)
    {
        $this->name = $name;
        $this->url  = $url;
        $this->logo = $logo;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * The name of the file in public/img/sponsors/.
     *
     * @return non-empty-string
     */
    public function logo(): string
    {
        return $this->logo;
    }
}
