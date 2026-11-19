<?php declare(strict_types=1);

return [
    'majorVersions' => [
        [
            'version'           => 2,
            'released'          => '2021-02-05',
            'minimumPhpVersion' => '8.0',
            'minorVersions'     => [
                ['version' => '2.4', 'released' => '2022-06-03', 'milestone' => 7],
                ['version' => '2.3', 'released' => '2021-08-06'],
                ['version' => '2.10', 'released' => '2022-12-02', 'milestone' => 8],
            ],
            'endOfLife'    => null,
            'deprecations' => true,
        ],
        [
            'version'           => 3,
            'released'          => '2023-02-03',
            'minimumPhpVersion' => '8.2',
            'milestone'         => 9,
            'endOfLife'         => null,
            'deprecations'      => true,
        ],
        [
            'version'           => 1,
            'released'          => '2019-02-01',
            'minimumPhpVersion' => '7.2',
            'maximumPhpVersion' => '7.4',
            'minorVersions'     => [
                ['version' => '1.1', 'released' => '2019-04-05'],
                ['version' => '1.2', 'released' => '2019-06-07', 'announcement' => 'https://example.org/1.2.html'],
            ],
            'endOfBugfixSupport' => '2020-12-31',
            'endOfLife'          => '2021-12-31',
            'documentation'      => 'https://example.org/manual/1.2/',
        ],
    ],
    'announcements' => [
        ['date' => '2021-06-01', 'title' => 'Notice & news', 'page' => 'notice.html', 'text' => 'Something was announced.'],
        ['date' => '2022-12-01', 'title' => 'Not announced yet', 'page' => 'later.html', 'text' => 'Something will be announced.'],
    ],
    'articles' => [
        ['date' => '2022-01-10', 'title' => 'Older article', 'url' => 'https://example.org/older.html'],
        ['date' => '2022-03-01', 'title' => 'Tips & tricks', 'url' => 'https://example.org/tips.html?a=1&b=2'],
        ['date' => '2022-09-01', 'title' => 'Not published yet', 'url' => 'https://example.org/future.html'],
    ],
    'sponsors' => [
        'business' => [
            ['name' => 'Acme Inc.', 'url' => 'https://example.org/acme', 'logo' => 'acme.svg'],
        ],
        'infrastructure' => [
            ['name' => 'Hoster', 'url' => 'https://example.org/hoster', 'logo' => 'hoster.svg'],
        ],
    ],
    'sitemap' => [
        'exclude' => ['excluded.html', 'excluded/'],
    ],
];
