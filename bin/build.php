#!/usr/bin/env php
<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

use const PHP_EOL;
use const STDERR;
use function fwrite;
use function sprintf;
use DateTimeImmutable;

require __DIR__ . '/../vendor/autoload.php';

$today = new DateTimeImmutable('today');

if (isset($argv[1])) {
    $today = DateTimeImmutable::createFromFormat('!Y-m-d', $argv[1]);

    if ($today === false || $today->format('Y-m-d') !== $argv[1]) {
        fwrite(
            STDERR,
            sprintf(
                '"%s" is not a date in the format YYYY-MM-DD' . PHP_EOL,
                $argv[1],
            ),
        );

        exit(1);
    }
}

try {
    $factory = new Factory(
        $today,
        __DIR__ . '/../config.php',
        __DIR__ . '/../templates',
        __DIR__ . '/../public',
        new GitPageHistory(__DIR__ . '/../public'),
    );

    $factory->renderer()->render();
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);

    exit(1);
}
