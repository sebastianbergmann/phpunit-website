<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function assert;
use function exec;
use function file_put_contents;
use function implode;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitPageHistory::class)]
#[Medium]
final class GitPageHistoryTest extends TestCase
{
    /**
     * @var non-empty-string
     */
    private string $repository;

    protected function setUp(): void
    {
        $this->repository = sys_get_temp_dir() . '/' . uniqid('phpunit-website-');

        mkdir($this->repository . '/public', 0o777, true);

        $this->git('init --quiet');

        file_put_contents($this->repository . '/public/page.html', 'first');

        $this->git('add public/page.html');
        $this->commit('2022-03-04T10:00:00+01:00');
    }

    protected function tearDown(): void
    {
        $this->remove($this->repository);
    }

    public function testPageWasLastChangedOnTheDayOfTheLastCommitThatChangedIt(): void
    {
        file_put_contents($this->repository . '/public/other.html', 'other');

        $this->git('add public/other.html');
        $this->commit('2022-06-07T10:00:00+02:00');

        $history = new GitPageHistory($this->repository . '/public');
        $today   = new DateTimeImmutable('2022-12-01');

        $this->assertSame('2022-03-04', $history->lastModified('page.html', $today)->format('Y-m-d'));
        $this->assertSame('2022-06-07', $history->lastModified('other.html', $today)->format('Y-m-d'));
    }

    public function testPageWithChangesThatAreNotCommittedWasLastChangedToday(): void
    {
        file_put_contents($this->repository . '/public/page.html', 'second');

        $history = new GitPageHistory($this->repository . '/public');
        $today   = new DateTimeImmutable('2022-12-01');

        $this->assertSame($today, $history->lastModified('page.html', $today));
    }

    public function testPageThatIsNotCommittedYetWasLastChangedToday(): void
    {
        file_put_contents($this->repository . '/public/new.html', 'new');

        $history = new GitPageHistory($this->repository . '/public');
        $today   = new DateTimeImmutable('2022-12-01');

        $this->assertSame($today, $history->lastModified('new.html', $today));
    }

    public function testPageThatGitDoesNotKnowHasNoHistory(): void
    {
        $history = new GitPageHistory($this->repository . '/public');

        $this->expectException(PageHistoryException::class);
        $this->expectExceptionMessageIsOrContains('Git does not know does-not-exist.html');

        $history->lastModified('does-not-exist.html', new DateTimeImmutable('2022-12-01'));
    }

    public function testDirectoryHasToBeInsideGitRepository(): void
    {
        $directory = sys_get_temp_dir() . '/' . uniqid('phpunit-website-');

        mkdir($directory);

        $history = new GitPageHistory($directory);

        try {
            $this->expectException(PageHistoryException::class);
            $this->expectExceptionMessageIsOrContains('git status failed');

            $history->lastModified('page.html', new DateTimeImmutable('2022-12-01'));
        } finally {
            rmdir($directory);
        }
    }

    /**
     * @param non-empty-string $arguments
     */
    private function git(string $arguments, string $environment = ''): void
    {
        exec(
            sprintf(
                '%s git -C %s -c user.name=Test -c user.email=test@example.org -c commit.gpgsign=false %s 2>&1',
                $environment,
                $this->repository,
                $arguments,
            ),
            $output,
            $exitCode,
        );

        $this->assertSame(0, $exitCode, implode("\n", $output));
    }

    /**
     * @param non-empty-string $date
     */
    private function commit(string $date): void
    {
        $this->git(
            'commit --quiet --no-verify -m Commit',
            sprintf('GIT_AUTHOR_DATE=%1$s GIT_COMMITTER_DATE=%1$s', $date),
        );
    }

    private function remove(string $directory): void
    {
        $entries = scandir($directory);

        assert($entries !== false);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . '/' . $entry;

            if (is_dir($path)) {
                $this->remove($path);

                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
