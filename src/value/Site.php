<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

/**
 * Everything that config.php configures.
 */
final readonly class Site
{
    private MajorVersionCollection $majorVersions;

    /**
     * @var list<Article>
     */
    private array $articles;

    /**
     * @var list<Sponsor>
     */
    private array $businessSponsors;

    /**
     * @var list<Sponsor>
     */
    private array $infrastructureSponsors;

    /**
     * @param list<Article> $articles
     * @param list<Sponsor> $businessSponsors
     * @param list<Sponsor> $infrastructureSponsors
     */
    public function __construct(MajorVersionCollection $majorVersions, array $articles, array $businessSponsors, array $infrastructureSponsors)
    {
        $this->majorVersions          = $majorVersions;
        $this->articles               = $articles;
        $this->businessSponsors       = $businessSponsors;
        $this->infrastructureSponsors = $infrastructureSponsors;
    }

    public function majorVersions(): MajorVersionCollection
    {
        return $this->majorVersions;
    }

    /**
     * @return list<Article>
     */
    public function articles(): array
    {
        return $this->articles;
    }

    /**
     * @return list<Sponsor>
     */
    public function businessSponsors(): array
    {
        return $this->businessSponsors;
    }

    /**
     * @return list<Sponsor>
     */
    public function infrastructureSponsors(): array
    {
        return $this->infrastructureSponsors;
    }
}
