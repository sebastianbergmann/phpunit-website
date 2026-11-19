<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function fclose;
use function is_resource;
use function proc_close;
use function proc_open;
use function sprintf;
use function stream_get_contents;
use function trim;
use DateTimeImmutable;

/**
 * Asks Git when a file was last changed: this is the day of the last commit
 * that changed it, or today when the file has changes that are not
 * committed, which is what a build leaves behind before it is reviewed.
 */
final readonly class GitPageHistory implements PageHistory
{
    /**
     * @var non-empty-string
     */
    private string $directory;

    /**
     * @param non-empty-string $directory the document root, inside a Git repository
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public function lastModified(string $path, DateTimeImmutable $today): DateTimeImmutable
    {
        if ($this->git(['status', '--porcelain', '--', $path]) !== '') {
            return $today;
        }

        $date = $this->git(['log', '-1', '--format=%cs', '--', $path]);

        if ($date === '') {
            throw new PageHistoryException(
                sprintf(
                    'Git does not know %s',
                    $path,
                ),
            );
        }

        $lastModified = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        if ($lastModified === false) {
            throw new PageHistoryException(
                sprintf(
                    'Git reports "%s" as the day %s was last changed',
                    $date,
                    $path,
                ),
            );
        }

        return $lastModified;
    }

    /**
     * @param non-empty-list<non-empty-string> $arguments
     *
     * @throws PageHistoryException
     */
    private function git(array $arguments): string
    {
        $process = @proc_open(
            ['git', ...$arguments],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->directory,
        );

        if (!is_resource($process) || !isset($pipes[1], $pipes[2])) {
            throw new PageHistoryException('Git cannot be run');
        }

        $output = stream_get_contents($pipes[1]);
        $error  = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        if (proc_close($process) !== 0 || $output === false) {
            throw new PageHistoryException(
                sprintf(
                    'git %s failed in %s: %s',
                    $arguments[0],
                    $this->directory,
                    trim((string) $error),
                ),
            );
        }

        return trim($output);
    }
}
