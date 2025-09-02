#!/usr/bin/env php
<?php declare(strict_types=1);
$items = items();

$writer = new XMLWriter;

$writer->openMemory();
$writer->setIndent(true);
$writer->startDocument('1.0', 'UTF-8');

$writer->startElement('rdf:RDF');
$writer->writeAttribute('xmlns', 'http://purl.org/rss/1.0/');
$writer->writeAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
$writer->writeAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
$writer->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');

$writer->startElement('channel');
$writer->writeAttribute('rdf:about', 'https://phpunit.de/releases.rss');
$writer->writeElement('link', 'https://phpunit.de/releases.rss');
$writer->writeElement('dc:creator', 'sebastian@phpunit.de');
$writer->writeElement('dc:publisher', 'sebastian@phpunit.de');
$writer->writeElement('dc:language', 'en');

$writer->startElement('items');
$writer->startElement('rdf:Seq');

foreach ($items as $item) {
    $writer->startElement('rdf:li');
    $writer->writeAttribute('rdf:resource', $item['url']);
    $writer->endElement();
}

$writer->endElement();
$writer->endElement();

$writer->writeElement('title', 'Latest PHPUnit Releases');
$writer->writeElement('description', 'The latest releases of PHPUnit');

$writer->endElement();

foreach ($items as $item) {
    $writer->startElement('item');
    $writer->writeAttribute('rdf:about', $item['url']);

    $writer->writeElement('title', $item['name'] . ' has been released');
    $writer->writeElement('link', $item['url']);

    $writer->writeElement(
        'content:encoded',
        sprintf(
            <<<'EOT'
<p>%s has been released. Details about this release are available in the <a href="%s">ChangeLog</a>.</p>
<p><strong>Keep up to date with PHPUnit:</strong></p>
<ul>
    <li>You can follow <a href="https://phpc.social/@phpunit">@phpunit@phpc.social</a> to stay up to date with PHPUnit's development.</li>
    <li>You can subscribe to the <a href="https://t8cbf4509.emailsys1a.net/275/973/33ad04f4be/subscribe/form.html?_g=1752156344">PHPUnit Updates</a> newsletter to receive updates about and tips for PHPUnit.</li>
</ul>
EOT,
            $item['name'],
            $item['url'],
        )
    );

    $writer->writeElement('dc:date', $item['date']->format(DATE_W3C));

    $writer->endElement();
}

$writer->endElement();

$writer->endDocument();

file_put_contents(
    __DIR__ . '/../public/releases.rss',
    $writer->outputMemory(),
);

print 'Wrote ' . realpath(__DIR__ . '/../public/releases.rss') . PHP_EOL;

/**
 * @return non-empty-list<array{name: non-empty-string, url: non-empty-string, date: DateTimeImmutable}>
 */
function items(): array
{
    $releases = json_decode(
        file_get_contents(
            'https://api.github.com/repos/sebastianbergmann/phpunit/releases',
            false,
            stream_context_create(
                [
                    'http' => [
                        'header' => [
                            'User-Agent: phpunit.de RSS Feed Generator\r\n',
                            'Accept: application/vnd.github+json\r\n'
                        ]
                    ]
                ]
            ),
        ),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    $items  = [];
    $series = [];

    foreach ($releases as $release) {
        if (isset($series[$release['target_commitish']])) {
            continue;
        }

        $items[] = [
                'name' => $release['name'],
                'url'  => $release['html_url'],
                'date' => new DateTimeImmutable($release['created_at']),
        ];

        $series[$release['target_commitish']] = true;
    }

    return $items;
}
