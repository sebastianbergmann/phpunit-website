<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use function file_get_contents;
use function is_file;
use function preg_match;
use function preg_quote;
use function preg_replace_callback;
use function sprintf;
use function str_contains;
use function str_replace;

/**
 * Fills the {{placeholders}} of a template.
 *
 * A placeholder that stands alone on a line is a block: the line, including
 * its line break, is replaced, so the value has to bring its own line break
 * and an empty value leaves nothing behind. Every other placeholder is
 * replaced where it stands.
 *
 * The header and the footer exist once, in templates/shared/. A page pulls
 * them in with {{header}} and {{footer}}.
 */
final readonly class TemplateRenderer
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
     * @param non-empty-string $template
     */
    public function has(string $template): bool
    {
        return is_file($this->directory . '/' . $template);
    }

    /**
     * @param non-empty-string                $template
     * @param array<non-empty-string, string> $values
     *
     * @throws TemplateException
     */
    public function render(string $template, array $values): string
    {
        $buffer = $this->load($template);

        foreach ($values as $name => $value) {
            $placeholder = '{{' . $name . '}}';

            if (!str_contains($buffer, $placeholder)) {
                throw new TemplateException(
                    sprintf(
                        'Template %s has no placeholder %s',
                        $template,
                        $placeholder,
                    ),
                );
            }

            $buffer = preg_replace_callback(
                '/^' . preg_quote($placeholder, '/') . '\n/m',
                static fn (): string => $value,
                $buffer,
            );

            if ($buffer === null) {
                throw new TemplateException(
                    sprintf(
                        'Placeholder %s of template %s cannot be replaced',
                        $placeholder,
                        $template,
                    ),
                );
            }

            $buffer = str_replace($placeholder, $value, $buffer);
        }

        if (preg_match('/{{[A-Za-z]+}}/', $buffer, $matches) === 1) {
            throw new TemplateException(
                sprintf(
                    'Placeholder %s of template %s was not replaced',
                    $matches[0],
                    $template,
                ),
            );
        }

        return $buffer;
    }

    /**
     * Renders templates/pages/<page>. The entry of the navigation that
     * links to the page is marked as the current one.
     *
     * @param non-empty-string                $page
     * @param array<non-empty-string, string> $values
     *
     * @throws TemplateException
     */
    public function renderPage(string $page, array $values): string
    {
        $values['header'] = $this->header($page);
        $values['footer'] = $this->load('shared/footer.html');

        return $this->render('pages/' . $page, $values);
    }

    /**
     * @param non-empty-string $page
     *
     * @throws TemplateException
     */
    private function header(string $page): string
    {
        $url = '/' . $page;

        if ($page === 'index.html') {
            $url = '/';
        }

        return str_replace(
            '<li><a href="' . $url . '">',
            '<li><a href="' . $url . '" aria-current="page">',
            $this->load('shared/header.html'),
        );
    }

    /**
     * @param non-empty-string $template
     *
     * @throws TemplateException
     */
    private function load(string $template): string
    {
        $file = $this->directory . '/' . $template;

        if (!is_file($file)) {
            throw new TemplateException(
                sprintf(
                    'Template %s does not exist',
                    $file,
                ),
            );
        }

        $buffer = file_get_contents($file);

        if ($buffer === false) {
            throw new TemplateException(
                sprintf(
                    'Template %s cannot be read',
                    $file,
                ),
            );
        }

        return $buffer;
    }
}
