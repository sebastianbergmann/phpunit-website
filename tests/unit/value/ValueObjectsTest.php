<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Announcement::class)]
#[CoversClass(Article::class)]
#[CoversClass(MinorVersion::class)]
#[CoversClass(Site::class)]
#[CoversClass(Sponsor::class)]
#[UsesClass(MajorVersion::class)]
#[UsesClass(MajorVersionCollection::class)]
final class ValueObjectsTest extends TestCase
{
    public function testAnnouncementIsPublishedOnItsDate(): void
    {
        $date         = new DateTimeImmutable('2014-04-21');
        $announcement = new Announcement($date, 'End of Life', 'end-of-life.html', 'It is over.');

        $this->assertSame($date, $announcement->date());
        $this->assertSame('End of Life', $announcement->title());
        $this->assertSame('end-of-life.html', $announcement->page());
        $this->assertSame('It is over.', $announcement->text());
        $this->assertFalse($announcement->isPublished(new DateTimeImmutable('2014-04-20')));
        $this->assertTrue($announcement->isPublished(new DateTimeImmutable('2014-04-21')));
    }

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

    public function testMinorVersionIsReleasedOnTheDayOfItsRelease(): void
    {
        $released     = new DateTimeImmutable('2026-12-04');
        $minorVersion = new MinorVersion('13.5', $released, 74, 'https://example.org/13.5.html');

        $this->assertSame('13.5', $minorVersion->version());
        $this->assertSame($released, $minorVersion->released());
        $this->assertSame(74, $minorVersion->milestone());
        $this->assertSame('https://example.org/13.5.html', $minorVersion->configuredAnnouncementUrl());
        $this->assertFalse($minorVersion->isReleased(new DateTimeImmutable('2026-12-03')));
        $this->assertTrue($minorVersion->isReleased(new DateTimeImmutable('2026-12-04')));
    }

    public function testMinorVersionNeedsNeitherMilestoneNorConfiguredAnnouncement(): void
    {
        $minorVersion = new MinorVersion('13.4', new DateTimeImmutable('2026-10-02'), null, null);

        $this->assertNull($minorVersion->milestone());
        $this->assertNull($minorVersion->configuredAnnouncementUrl());
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
                new MajorVersion(13, new DateTimeImmutable('2026-02-06'), '8.4', null, [], null, null, true, null, false, null),
            ],
        );

        $announcements  = [new Announcement(new DateTimeImmutable('2014-04-21'), 'Title', 'page.html', 'Text')];
        $articles       = [new Article(new DateTimeImmutable('2026-11-04'), 'Title', 'https://example.org/')];
        $business       = [new Sponsor('Acme Inc.', 'https://example.org/', 'acme.svg')];
        $infrastructure = [new Sponsor('Hoster', 'https://example.org/', 'hoster.svg')];

        $site = new Site($majorVersions, $announcements, $articles, $business, $infrastructure, ['impressum.html', 'manual/']);

        $this->assertSame($majorVersions, $site->majorVersions());
        $this->assertSame($announcements, $site->announcements());
        $this->assertSame($articles, $site->articles());
        $this->assertSame($business, $site->businessSponsors());
        $this->assertSame($infrastructure, $site->infrastructureSponsors());
        $this->assertSame(['impressum.html', 'manual/'], $site->pagesExcludedFromSitemap());
    }
}
