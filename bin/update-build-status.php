#!/usr/bin/env php
<?php declare(strict_types=1);
$next    = '9.1';
$current = '9.0';
$old     = '8.5';

$nextRepositories = [
    'phpunit'                  => 'master',
    'php-code-coverage'        => 'master',
    'code-unit'                => 'master',
    'code-unit-reverse-lookup' => 'master',
    'comparator'               => 'master',
    'diff'                     => 'master',
    'environment'              => 'master',
    'exporter'                 => 'master',
    'global-state'             => 'master',
    'object-enumerator'        => 'master',
    'object-reflector'         => 'master',
    #'php-file-iterator'        => 'master',
    'php-invoker'              => 'master',
    #'php-text-template'        => 'master',
    'php-timer'                => 'master',
    'php-token-stream'         => 'master',
    'recursion-context'        => 'master',
    #'resource-operations'      => 'master',
    'type'                     => 'master',
    #'version'                  => 'master',
];

$currentRepositories = [
    'phpunit'                  => '9.0',
    'php-code-coverage'        => 'master',
    'code-unit-reverse-lookup' => 'master',
    'comparator'               => 'master',
    'diff'                     => 'master',
    'environment'              => 'master',
    'exporter'                 => 'master',
    'global-state'             => 'master',
    'object-enumerator'        => 'master',
    'object-reflector'         => 'master',
    #'php-file-iterator'        => 'master',
    'php-invoker'              => 'master',
    #'php-text-template'        => 'master',
    'php-timer'                => 'master',
    'php-token-stream'         => 'master',
    'recursion-context'        => 'master',
    #'resource-operations'      => 'master',
    'type'                     => 'master',
    #'version'                  => 'master',
];

$oldRepositories = [
    'phpunit'                  => '8.5',
    'php-code-coverage'        => '7.0',
    'code-unit-reverse-lookup' => '1.0',
    'comparator'               => '3.0',
    'diff'                     => '3.0',
    'environment'              => '4.2',
    'exporter'                 => '3.1',
    'global-state'             => '3.0',
    'object-enumerator'        => '3.0',
    'object-reflector'         => '2.0',
    #'php-file-iterator'        => '2.0',
    'php-invoker'              => '2.0',
    #'php-text-template'        => '1.2',
    'php-timer'                => '2.1',
    'php-token-stream'         => '3.1',
    'recursion-context'        => '3.0',
    #'resource-operations'      => '2.0',
    'type'                     => '1.1',
    #'version'                  => '2.0',
];

\file_put_contents(
    __DIR__ . '/../public/build-status.html',
    \str_replace(
        [
            '{{next}}',
            '{{next_items}}',
            '{{current}}',
            '{{current_items}}',
            '{{old}}',
            '{{old_items}}',
        ],
        [
            $next,
            render($nextRepositories),
            $current,
            render($currentRepositories),
            $old,
            render($oldRepositories),
        ],
        \file_get_contents(__DIR__ . '/../templates/build-status-page.html')
    )
);

function render(array $repositories): string
{
    $buffer = '';

    foreach ($repositories as $repository => $branch) {
        $buffer .= \str_replace(
            [
                '{{repository}}',
                '{{branch}}',
            ],
            [
                $repository,
                $branch,
            ],
            \file_get_contents(__DIR__ . '/../templates/build-status-item.html')
        );
    }

    return $buffer;
}
