<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

/**
 * Everything that config.php configures.
 */
final readonly class Site
{
    private MajorVersionCollection $majorVersions;

    /**
     * @var list<Announcement>
     */
    private array $announcements;

    /**
     * @var list<Article>
     */
    private array $articles;

    /**
     * @var list<CodeSprint>
     */
    private array $codeSprints;

    /**
     * @var list<Sponsor>
     */
    private array $businessSponsors;

    /**
     * @var list<Sponsor>
     */
    private array $infrastructureSponsors;

    /**
     * @var list<non-empty-string>
     */
    private array $pagesExcludedFromSitemap;

    /**
     * @param list<Announcement>     $announcements
     * @param list<Article>          $articles
     * @param list<CodeSprint>       $codeSprints
     * @param list<Sponsor>          $businessSponsors
     * @param list<Sponsor>          $infrastructureSponsors
     * @param list<non-empty-string> $pagesExcludedFromSitemap
     */
    public function __construct(MajorVersionCollection $majorVersions, array $announcements, array $articles, array $codeSprints, array $businessSponsors, array $infrastructureSponsors, array $pagesExcludedFromSitemap)
    {
        $this->majorVersions            = $majorVersions;
        $this->announcements            = $announcements;
        $this->articles                 = $articles;
        $this->codeSprints              = $codeSprints;
        $this->businessSponsors         = $businessSponsors;
        $this->infrastructureSponsors   = $infrastructureSponsors;
        $this->pagesExcludedFromSitemap = $pagesExcludedFromSitemap;
    }

    public function majorVersions(): MajorVersionCollection
    {
        return $this->majorVersions;
    }

    /**
     * The announcements that are not the release of a version.
     *
     * @return list<Announcement>
     */
    public function announcements(): array
    {
        return $this->announcements;
    }

    /**
     * @return list<Article>
     */
    public function articles(): array
    {
        return $this->articles;
    }

    /**
     * @return list<CodeSprint>
     */
    public function codeSprints(): array
    {
        return $this->codeSprints;
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

    /**
     * A page, or a directory when it ends with a slash, relative to the
     * document root.
     *
     * @return list<non-empty-string>
     */
    public function pagesExcludedFromSitemap(): array
    {
        return $this->pagesExcludedFromSitemap;
    }
}
