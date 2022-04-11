#!/usr/bin/env php
<?php declare(strict_types=1);
$next    = '10.0';
$current = '9.5';
$old     = '8.5';

$nextRepositories = [
    'phpunit'                  => 'main',
    'php-code-coverage'        => 'main',
    'cli-parser'               => 'main',
    'code-unit'                => 'main',
    'code-unit-reverse-lookup' => 'main',
    'comparator'               => 'main',
    'complexity'               => 'main',
    'diff'                     => 'main',
    'environment'              => 'main',
    'exporter'                 => 'main',
    'global-state'             => 'main',
    'lines-of-code'            => 'main',
    'object-enumerator'        => 'main',
    'object-reflector'         => 'main',
    'php-file-iterator'        => 'main',
    'php-invoker'              => 'main',
    'php-text-template'        => 'main',
    'php-timer'                => 'main',
    'php-token-stream'         => '',
    'recursion-context'        => 'main',
    #'resource-operations'      => 'main',
    'type'                     => 'main',
    #'version'                  => 'main',
];

$currentRepositories = [
    'phpunit'                  => '9.5',
    'php-code-coverage'        => 'main',
    'cli-parser'               => 'main',
    'code-unit'                => 'main',
    'code-unit-reverse-lookup' => 'main',
    'comparator'               => 'main',
    'complexity'               => 'main',
    'diff'                     => 'main',
    'environment'              => 'main',
    'exporter'                 => 'main',
    'global-state'             => 'main',
    'lines-of-code'            => 'main',
    'object-enumerator'        => 'main',
    'object-reflector'         => 'main',
    'php-file-iterator'        => 'main',
    'php-invoker'              => 'main',
    'php-text-template'        => 'main',
    'php-timer'                => 'main',
    'php-token-stream'         => '',
    'recursion-context'        => 'main',
    #'resource-operations'      => 'main',
    'type'                     => 'main',
    #'version'                  => 'main',
];

$oldRepositories = [
    'phpunit'                  => '8.5',
    'php-code-coverage'        => '7.0',
    'cli-parser'               => '',
    'code-unit'                => '',
    'code-unit-reverse-lookup' => '1.0',
    'comparator'               => '3.0',
    'complexity'               => '',
    'diff'                     => '3.0',
    'environment'              => '4.2',
    'exporter'                 => '3.1',
    'global-state'             => '3.0',
    'lines-of-code'            => '',
    'object-enumerator'        => '3.0',
    'object-reflector'         => '2.0',
    'php-file-iterator'        => '2.0',
    'php-invoker'              => '2.0',
    'php-text-template'        => '1.2',
    'php-timer'                => '2.1',
    'php-token-stream'         => '3.1',
    'recursion-context'        => '3.0',
    #'resource-operations'      => '2.0',
    'type'                     => '1.1',
    #'version'                  => '2.0',
];

$repositories = '';

foreach (array_keys($nextRepositories) as $repository) {
    $repositories .= \str_replace(
        [
            '{{repository}}',
            '{{next_part}}',
            '{{current_part}}',
            '{{old_part}}',
        ],
        [
            $repository,
            part($repository, $nextRepositories[$repository]),
            part($repository, $currentRepositories[$repository]),
            part($repository, $oldRepositories[$repository]),
        ],
        \file_get_contents(__DIR__ . '/../templates/build-status-item.html')
    );
}

\file_put_contents(
    __DIR__ . '/../public/build-status.html',
    \str_replace(
        [
            '{{repositories}}',
            '{{next}}',
            '{{current}}',
            '{{old}}',
        ],
        [
            $repositories,
            $next,
            $current,
            $old
        ],
        \file_get_contents(__DIR__ . '/../templates/build-status-page.html')
    )
);

function part(string $repository, string $branch): string
{
    if (empty($branch)) {
        return \file_get_contents(__DIR__ . '/../templates/build-status-item-empty-part.html');
    }

    return \str_replace(
        [
            '{{repository}}',
            '{{branch}}',
        ],
        [
            $repository,
            $branch
        ],
        \file_get_contents(__DIR__ . '/../templates/build-status-item-part.html')
    );
}
