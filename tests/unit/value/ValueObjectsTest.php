<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Article::class)]
#[CoversClass(PlannedMinorVersion::class)]
#[CoversClass(Site::class)]
#[CoversClass(Sponsor::class)]
#[UsesClass(MajorVersion::class)]
#[UsesClass(MajorVersionCollection::class)]
final class ValueObjectsTest extends TestCase
{
    public function testArticleIsPublishedOnItsDate(): void
    {
        $date    = new DateTimeImmutable('2026-11-04');
        $article = new Article($date, 'What is new in PHPUnit 13.4?', 'https://example.org/article.html');

        $this->assertSame($date, $article->date());
        $this->assertSame('What is new in PHPUnit 13.4?', $article->title());
        $this->assertSame('https://example.org/article.html', $article->url());
        $this->assertFalse($article->isPublished(new DateTimeImmutable('2026-11-03')));
        $this->assertTrue($article->isPublished(new DateTimeImmutable('2026-11-04')));
    }

    public function testPlannedMinorVersionIsReleasedOnTheDayOfItsRelease(): void
    {
        $released = new DateTimeImmutable('2026-12-04');
        $planned  = new PlannedMinorVersion('13.5', $released, 74);

        $this->assertSame('13.5', $planned->version());
        $this->assertSame($released, $planned->released());
        $this->assertSame(74, $planned->milestone());
        $this->assertFalse($planned->isReleased(new DateTimeImmutable('2026-12-03')));
        $this->assertTrue($planned->isReleased(new DateTimeImmutable('2026-12-04')));
    }

    public function testSponsorHasNameUrlAndLogo(): void
    {
        $sponsor = new Sponsor('Acme Inc.', 'https://example.org/', 'acme.svg');

        $this->assertSame('Acme Inc.', $sponsor->name());
        $this->assertSame('https://example.org/', $sponsor->url());
        $this->assertSame('acme.svg', $sponsor->logo());
    }

    public function testSiteHasWhatIsConfigured(): void
    {
        $majorVersions = MajorVersionCollection::fromList(
            [
                new MajorVersion(13, new DateTimeImmutable('2026-02-06'), '8.4', null, '13.4', null, null, null, null, false, null, []),
            ],
        );

        $articles       = [new Article(new DateTimeImmutable('2026-11-04'), 'Title', 'https://example.org/')];
        $business       = [new Sponsor('Acme Inc.', 'https://example.org/', 'acme.svg')];
        $infrastructure = [new Sponsor('Hoster', 'https://example.org/', 'hoster.svg')];

        $site = new Site($majorVersions, $articles, $business, $infrastructure);

        $this->assertSame($majorVersions, $site->majorVersions());
        $this->assertSame($articles, $site->articles());
        $this->assertSame($business, $site->businessSponsors());
        $this->assertSame($infrastructure, $site->infrastructureSponsors());
    }
}
