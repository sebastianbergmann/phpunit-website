<?php declare(strict_types=1);

/*
 * The single source of truth for everything on phpunit.de that changes with
 * a release, an announcement, a code sprint, a sponsor, or an article. Run bin/build.php
 * after changing it.
 *
 * majorVersions
 *
 *   version              major version number
 *   released             day of the first release; a day in the future makes
 *                        this the next major version until that day
 *   minimumPhpVersion    oldest version of PHP it runs on
 *   maximumPhpVersion    newest version of PHP it runs on (only for major
 *                        versions that do not get Life Support anymore)
 *   minorVersions        the minor versions after the first one (13.1, but
 *                        not 13.0), each with
 *                          version       version number
 *                          released      day of the release; a day in the
 *                                        future makes this the next minor
 *                                        version until that day
 *                          milestone     number of the milestone on GitHub
 *                                        (required while the minor version
 *                                        is not released)
 *                          announcement  URL of the announcement when it is
 *                                        not the release on GitHub (as of
 *                                        PHPUnit 11) or the section of the
 *                                        ChangeLog (before PHPUnit 11)
 *   milestone            number of the milestone on GitHub (required while
 *                        the major version is not released)
 *   endOfLife            day Life Support ended, null while it lasts
 *   endOfBugfixSupport   only when it is not the day the major version after
 *                        the next one was released
 *   documentation        URL of the documentation when it is not
 *                        https://docs.phpunit.de/en/<newest minor version>/,
 *                        null when there is none
 *   deprecations         true when the branch of the newest minor version
 *                        has a DEPRECATIONS.md
 *
 * The status of a major version, the end of its Bugfix Support, the current
 * major version, the newest minor version of a major version, the next minor
 * and major version, and the index of the announcements follow from these
 * dates and the day of the build.
 */
return [
    'majorVersions' => [
        [
            'version'           => 14,
            'released'          => '2027-02-05',
            'minimumPhpVersion' => '8.5',
            'milestone'         => 75,
            'endOfLife'         => null,
            'deprecations'      => true,
        ],
        [
            'version'           => 13,
            'released'          => '2026-02-06',
            'minimumPhpVersion' => '8.4',
            'minorVersions'     => [
                ['version' => '13.1', 'released' => '2026-04-03'],
                ['version' => '13.2', 'released' => '2026-06-05'],
                ['version' => '13.3', 'released' => '2026-08-07'],
                ['version' => '13.4', 'released' => '2026-10-02'],
                ['version' => '13.5', 'released' => '2026-12-04', 'milestone' => 74],
            ],
            'endOfLife'    => null,
            'deprecations' => true,
        ],
        [
            'version'           => 12,
            'released'          => '2025-02-07',
            'minimumPhpVersion' => '8.3',
            'minorVersions'     => [
                ['version' => '12.1', 'released' => '2025-04-04'],
                ['version' => '12.2', 'released' => '2025-06-06'],
                ['version' => '12.3', 'released' => '2025-08-01'],
                ['version' => '12.4', 'released' => '2025-10-03'],
                ['version' => '12.5', 'released' => '2025-12-05'],
            ],
            'endOfLife'    => null,
            'deprecations' => true,
        ],
        [
            'version'           => 11,
            'released'          => '2024-02-02',
            'minimumPhpVersion' => '8.2',
            'minorVersions'     => [
                ['version' => '11.1', 'released' => '2024-04-05'],
                ['version' => '11.2', 'released' => '2024-06-07'],
                ['version' => '11.3', 'released' => '2024-08-02'],
                ['version' => '11.4', 'released' => '2024-10-05'],
                ['version' => '11.5', 'released' => '2024-12-06'],
            ],
            'endOfLife'    => null,
            'deprecations' => true,
        ],
        [
            'version'           => 10,
            'released'          => '2023-02-03',
            'minimumPhpVersion' => '8.1',
            'minorVersions'     => [
                ['version' => '10.1', 'released' => '2023-04-14'],
                ['version' => '10.2', 'released' => '2023-06-02'],
                ['version' => '10.3', 'released' => '2023-08-04'],
                ['version' => '10.4', 'released' => '2023-10-06'],
                ['version' => '10.5', 'released' => '2023-12-01'],
            ],
            'endOfLife'    => null,
            'deprecations' => true,
        ],
        [
            'version'           => 9,
            'released'          => '2020-02-07',
            'minimumPhpVersion' => '7.3',
            'minorVersions'     => [
                ['version' => '9.1', 'released' => '2020-04-03'],
                ['version' => '9.2', 'released' => '2020-06-05'],
                ['version' => '9.3', 'released' => '2020-08-07'],
                ['version' => '9.4', 'released' => '2020-10-02'],
                ['version' => '9.5', 'released' => '2020-12-04'],
                ['version' => '9.6', 'released' => '2023-02-03'],
            ],
            'endOfLife'    => null,
            'deprecations' => true,
        ],
        [
            'version'           => 8,
            'released'          => '2019-02-01',
            'minimumPhpVersion' => '7.2',
            'minorVersions'     => [
                ['version' => '8.1', 'released' => '2019-04-05'],
                ['version' => '8.2', 'released' => '2019-06-07'],
                ['version' => '8.3', 'released' => '2019-08-02'],
                ['version' => '8.4', 'released' => '2019-10-04'],
                ['version' => '8.5', 'released' => '2019-12-06'],
            ],
            'endOfLife' => null,
        ],
        [
            'version'           => 7,
            'released'          => '2018-02-02',
            'minimumPhpVersion' => '7.1',
            'maximumPhpVersion' => '7.3',
            'minorVersions'     => [
                ['version' => '7.1', 'released' => '2018-04-06'],
                ['version' => '7.2', 'released' => '2018-06-01'],
                ['version' => '7.3', 'released' => '2018-08-03'],
                ['version' => '7.4', 'released' => '2018-10-05'],
                ['version' => '7.5', 'released' => '2018-12-07'],
            ],
            'endOfLife' => '2020-02-07',
        ],
        [
            'version'           => 6,
            'released'          => '2017-02-03',
            'minimumPhpVersion' => '7.0',
            'maximumPhpVersion' => '7.2',
            'minorVersions'     => [
                ['version' => '6.1', 'released' => '2017-04-07'],
                ['version' => '6.2', 'released' => '2017-06-02'],
                ['version' => '6.3', 'released' => '2017-08-04'],
                ['version' => '6.4', 'released' => '2017-10-06'],
                ['version' => '6.5', 'released' => '2017-12-01'],
            ],
            'endOfLife'     => '2019-02-01',
            'documentation' => 'https://phpunit.de/manual/6.5/en/index.html',
        ],
        [
            'version'           => 5,
            'released'          => '2015-10-02',
            'minimumPhpVersion' => '5.6',
            'maximumPhpVersion' => '7.1',
            'minorVersions'     => [
                ['version' => '5.1', 'released' => '2015-12-04'],
                ['version' => '5.2', 'released' => '2016-02-05'],
                ['version' => '5.3', 'released' => '2016-04-01'],
                ['version' => '5.4', 'released' => '2016-06-03'],
                ['version' => '5.5', 'released' => '2016-08-05'],
                ['version' => '5.6', 'released' => '2016-10-07'],
                ['version' => '5.7', 'released' => '2016-12-02'],
            ],
            'endOfLife'     => '2018-02-02',
            'documentation' => 'https://phpunit.de/manual/5.7/en/index.html',
        ],
        [
            'version'           => 4,
            'released'          => '2014-03-07',
            'minimumPhpVersion' => '5.3',
            'maximumPhpVersion' => '5.6',
            // PHPUnit 4.1 to PHPUnit 4.5 were not announced on the website
            'minorVersions' => [
                ['version' => '4.6', 'released' => '2015-04-03', 'announcement' => 'https://github.com/sebastianbergmann/phpunit/blob/4.6.0/ChangeLog-4.6.md#phpunit-460'],
                ['version' => '4.7', 'released' => '2015-06-05', 'announcement' => 'https://github.com/sebastianbergmann/phpunit/blob/4.7.0/ChangeLog-4.7.md#phpunit-470'],
                ['version' => '4.8', 'released' => '2015-08-07'],
            ],
            'endOfLife'     => '2017-02-03',
            'documentation' => 'https://phpunit.de/manual/4.8/en/index.html',
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
            'documentation'      => null,
        ],
    ],

    /*
     * Announcements that are not the release of a version. The release of a
     * major version is announced when public/announcements/ has a page for
     * it (phpunit-<version>.html), the release of a minor version always is.
     *
     * A page is the name of a file in public/announcements/.
     */
    'announcements' => [
        [
            'date'  => '2014-04-21',
            'title' => 'End of Life for PEAR Installation Method',
            'page'  => 'end-of-life-for-pear-installation-method.html',
            'text'  => 'PHPUnit can no longer be installed using the PEAR Installer.',
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
     * A code sprint that is not over on the day of the build is the next
     * event. Its page is named after the month and the year it starts in,
     * for instance april-2025.html. The photos and quotes of that page are
     * not configured here: they are the optional template
     * templates/code-sprints/impressions/april-2025.html.
     *
     * A logo is the name of a file in public/img/sponsors/.
     */
    'codeSprints' => [
        [
            'start'      => '2025-04-25',
            'end'        => '2025-04-26',
            'location'   => 'Berlin, Germany',
            'organizers' => [
                ['name' => 'CHECK24', 'url' => 'https://opensource.check24.de/', 'logo' => 'check24.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
        ],
        [
            'start'      => '2024-10-18',
            'end'        => '2024-10-19',
            'location'   => 'Munich, Germany',
            'organizers' => [
                ['name' => 'celebrate company', 'url' => 'https://celebrate.company/', 'logo' => 'celebrate_company.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
        ],
        [
            'start'      => '2024-03-08',
            'end'        => '2024-03-09',
            'location'   => 'Hamburg, Germany',
            'organizers' => [
                ['name' => 'celebrate company', 'url' => 'https://celebrate.company/', 'logo' => 'celebrate_company.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
        ],
        [
            'start'      => '2019-09-06',
            'end'        => '2019-09-07',
            'location'   => 'Mannheim, Germany',
            'organizers' => [
                ['name' => 'bitExpert', 'url' => 'https://www.bitexpert.de/', 'logo' => 'bitexpert.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
        ],
        [
            'start'      => '2019-04-26',
            'end'        => '2019-04-27',
            'location'   => 'Würzburg, Germany',
            'organizers' => [
                ['name' => 'FLYERALARM', 'url' => 'https://www.flyeralarm.de/', 'logo' => 'flyeralarm.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
        ],
        [
            'start'      => '2018-09-07',
            'end'        => '2018-09-08',
            'location'   => 'Munich, Germany',
            'organizers' => [
                ['name' => 'CHECK24', 'url' => 'https://opensource.check24.de/', 'logo' => 'check24.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
        ],
        [
            'start'      => '2018-04-06',
            'end'        => '2018-04-07',
            'location'   => 'Hamburg, Germany',
            'organizers' => [
                ['name' => 'Kartenmacherei', 'url' => 'https://www.kartenmacherei.de/', 'logo' => 'kartenmacherei.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
        ],
        [
            'start'      => '2017-10-13',
            'end'        => '2017-10-14',
            'location'   => 'Düsseldorf, Germany',
            'organizers' => [
                ['name' => 'Trivago', 'url' => 'https://www.trivago.de/', 'logo' => 'trivago.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
        ],
        [
            'start'      => '2017-03-31',
            'end'        => '2017-04-01',
            'location'   => 'Hamburg, Germany',
            'organizers' => [
                ['name' => 'Kartenmacherei', 'url' => 'https://www.kartenmacherei.de/', 'logo' => 'kartenmacherei.svg'],
            ],
            'supporters' => [
                ['name' => 'thePHP.cc', 'url' => 'https://thephp.cc/?ref=phpunit.de', 'logo' => 'thephpcc.svg'],
            ],
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

    /*
     * The sitemap lists every HTML page below public/ with the day Git says
     * it was last changed. An exclusion is a page, or a directory when it
     * ends with a slash, relative to public/.
     */
    'sitemap' => [
        'exclude' => [
            'googleee2ea42e74d868fa.html',
            'manual/',
        ],
    ],
];
