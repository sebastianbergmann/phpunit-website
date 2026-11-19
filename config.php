<?php declare(strict_types=1);

/*
 * The single source of truth for everything on phpunit.de that changes with
 * a release, a sponsor, or an article. Run bin/build.php after changing it.
 *
 * majorVersions
 *
 *   version              major version number
 *   released             day of the first release; a day in the future makes
 *                        this the next major version until that day
 *   minimumPhpVersion    oldest version of PHP it runs on
 *   maximumPhpVersion    newest version of PHP it runs on (only for major
 *                        versions that do not get Life Support anymore)
 *   minorVersion         newest minor version that is released
 *   nextMinorVersion     the minor version after that: it replaces
 *                        minorVersion on the day of its release
 *   milestone            number of the milestone on GitHub (required while
 *                        the major version is not released)
 *   endOfLife            day Life Support ended, null while it lasts
 *   endOfBugfixSupport   only when it is not the day the major version after
 *                        the next one was released
 *   documentation        URL of the documentation when it is not
 *                        https://docs.phpunit.de/en/<minorVersion>/
 *   deprecations         true when the branch of the newest minor version
 *                        has a DEPRECATIONS.md
 *   tutorialPhpVersions  versions of PHP the "Getting Started" tutorial
 *                        names (required for the current major version)
 *
 * The status of a major version, the end of its Bugfix Support, the current
 * major version, and the next minor and major version follow from these
 * dates and the day of the build.
 */
return [
    'majorVersions' => [
        [
            'version'             => 14,
            'released'            => '2027-02-05',
            'minimumPhpVersion'   => '8.5',
            'minorVersion'        => '14.0',
            'milestone'           => 75,
            'endOfLife'           => null,
            'deprecations'        => true,
            'tutorialPhpVersions' => ['8.5'],
        ],
        [
            'version'           => 13,
            'released'          => '2026-02-06',
            'minimumPhpVersion' => '8.4',
            'minorVersion'      => '13.4',
            'nextMinorVersion'  => [
                'version'   => '13.5',
                'released'  => '2026-12-04',
                'milestone' => 74,
            ],
            'endOfLife'           => null,
            'deprecations'        => true,
            'tutorialPhpVersions' => ['8.4', '8.5'],
        ],
        [
            'version'           => 12,
            'released'          => '2025-02-07',
            'minimumPhpVersion' => '8.3',
            'minorVersion'      => '12.5',
            'endOfLife'         => null,
            'deprecations'      => true,
        ],
        [
            'version'           => 11,
            'released'          => '2024-02-02',
            'minimumPhpVersion' => '8.2',
            'minorVersion'      => '11.5',
            'endOfLife'         => null,
            'deprecations'      => true,
        ],
        [
            'version'           => 10,
            'released'          => '2023-02-03',
            'minimumPhpVersion' => '8.1',
            'minorVersion'      => '10.5',
            'endOfLife'         => null,
            'deprecations'      => true,
        ],
        [
            'version'           => 9,
            'released'          => '2020-02-07',
            'minimumPhpVersion' => '7.3',
            'minorVersion'      => '9.6',
            'endOfLife'         => null,
            'deprecations'      => true,
        ],
        [
            'version'           => 8,
            'released'          => '2019-02-01',
            'minimumPhpVersion' => '7.2',
            'minorVersion'      => '8.5',
            'endOfLife'         => null,
        ],
        [
            'version'           => 7,
            'released'          => '2018-02-02',
            'minimumPhpVersion' => '7.1',
            'maximumPhpVersion' => '7.3',
            'minorVersion'      => '7.5',
            'endOfLife'         => '2020-02-07',
        ],
        [
            'version'           => 6,
            'released'          => '2017-02-03',
            'minimumPhpVersion' => '7.0',
            'maximumPhpVersion' => '7.2',
            'endOfLife'         => '2019-02-01',
            'documentation'     => 'https://phpunit.de/manual/6.5/en/index.html',
        ],
        [
            'version'           => 5,
            'released'          => '2015-10-02',
            'minimumPhpVersion' => '5.6',
            'maximumPhpVersion' => '7.1',
            'endOfLife'         => '2018-02-02',
            'documentation'     => 'https://phpunit.de/manual/5.7/en/index.html',
        ],
        [
            'version'           => 4,
            'released'          => '2014-03-07',
            'minimumPhpVersion' => '5.3',
            'maximumPhpVersion' => '5.6',
            'endOfLife'         => '2017-02-03',
            'documentation'     => 'https://phpunit.de/manual/4.8/en/index.html',
        ],
        [
            'version'            => 3,
            'released'           => '2006-11-19',
            'minimumPhpVersion'  => '5.1',
            'maximumPhpVersion'  => '5.6',
            'endOfBugfixSupport' => '2014-10-17',
            'endOfLife'          => '2014-10-17',
            'documentation'      => 'https://phpunit.de/manual/3.7/en/index.html',
        ],
        [
            'version'            => 2,
            'released'           => '2004-07-14',
            'minimumPhpVersion'  => '5.0',
            'maximumPhpVersion'  => '5.1',
            'endOfBugfixSupport' => '2006-05-31',
            'endOfLife'          => '2006-05-31',
            'documentation'      => 'https://phpunit.de/manual/2.3/en/index.html',
        ],
        [
            'version'            => 1,
            'released'           => '2004-03-15',
            'minimumPhpVersion'  => '4.1',
            'maximumPhpVersion'  => '4.4',
            'endOfBugfixSupport' => '2007-05-13',
            'endOfLife'          => '2007-05-13',
        ],
    ],

    /*
     * The homepage shows the three newest articles that are published on the
     * day of the build.
     */
    'articles' => [
        [
            'date'  => '2026-11-04',
            'title' => 'What is new in PHPUnit 13.4?',
            'url'   => 'https://phpunit.expert/articles/order-and-focus-what-is-new-in-phpunit-134.html?ref=phpunit.de',
        ],
        [
            'date'  => '2026-10-27',
            'title' => 'Upgrading PHPUnit should be boring',
            'url'   => 'https://phpunit.expert/articles/upgrading-phpunit-should-be-boring.html?ref=phpunit.de',
        ],
        [
            'date'  => '2026-09-09',
            'title' => 'What is new in PHPUnit 13.3?',
            'url'   => 'https://phpunit.expert/articles/repetition-and-insight-what-is-new-in-phpunit-133.html?ref=phpunit.de',
        ],
    ],

    /*
     * A logo is the name of a file in public/img/sponsors/.
     */
    'sponsors' => [
        'business' => [
            ['name' => 'Bubble Shooter', 'url' => 'https://www.bubbleshooter.net/', 'logo' => 'bubble-shooter.png'],
            ['name' => 'in2it vof', 'url' => 'https://www.in2it.be/phpunit-supporter/', 'logo' => 'in2it.svg'],
            ['name' => 'Mailtrap', 'url' => 'https://l.rw.rw/php_unit', 'logo' => 'mailtrap.svg'],
            ['name' => 'PhpStorm', 'url' => 'https://jetbrains.com/phpstorm', 'logo' => 'phpstorm.svg'],
            ['name' => 'Testmo GmbH', 'url' => 'https://testmo.com/', 'logo' => 'testmo.svg'],
            ['name' => 'Tideways GmbH', 'url' => 'https://tideways.com/', 'logo' => 'tideways.svg'],
            ['name' => 'TYPO3 GmbH', 'url' => 'https://typo3.com/', 'logo' => 'typo3.svg'],
            ['name' => 'VEMA Versicherungsmakler Genossenschaft eG', 'url' => 'https://vema-eg.de/', 'logo' => 'vema.svg'],
        ],
        'infrastructure' => [
            ['name' => 'Cloudflare', 'url' => 'https://developers.cloudflare.com/sponsorships', 'logo' => 'cloudflare.svg'],
            ['name' => 'mittwald', 'url' => 'https://www.mittwald.de/', 'logo' => 'mittwald.svg'],
            ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
        ],
    ],
];
