<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function file_put_contents;
use function is_file;
use function sprintf;

/**
 * The document root: generated pages are written to it, and what is
 * maintained in it by hand (announcements, images) can be asked for.
 */
final readonly class PublicDirectory
{
    /**
     * @var non-empty-string
     */
    private string $directory;

    /**
     * @param non-empty-string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param non-empty-string $path
     */
    public function has(string $path): bool
    {
        return is_file($this->directory . '/' . $path);
    }

    /**
     * @param non-empty-string $path
     *
     * @throws TemplateException
     */
    public function write(string $path, string $content): void
    {
        if (file_put_contents($this->directory . '/' . $path, $content) === false) {
            throw new TemplateException(
                sprintf(
                    'Cannot write %s',
                    $this->directory . '/' . $path,
                ),
            );
        }
    }

    /**
     * The URL of the page that announces a major version, null when
     * public/announcements/ has no such page.
     *
     * @return ?non-empty-string
     */
    public function announcementUrl(MajorVersion $majorVersion): ?string
    {
        $path = sprintf('announcements/phpunit-%d.html', $majorVersion->version());

        if (!$this->has($path)) {
            return null;
        }

        return '/' . $path;
    }
}
