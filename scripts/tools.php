#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * This script downloads all needed dev tools as phar archives.
 */
$tools = [
    'phploc.phar'  => [
        'https://phar.phpunit.de/phploc-6.0.0.phar',
        '1d78dd081e4e895b28a8e3c54cfa85e6'
    ],
    'phpunit.phar' => [
        'https://phar.phpunit.de/phpunit-9.0.1.phar',
        'fe9704cd3a209f68f94538e2f1acefc6'
    ],
    'phpstan.phar' => [
        'https://github.com/phpstan/phpstan/releases/download/0.12.11/phpstan.phar',
        '5c29f61b4a38fd5747db7ed81a56aee9'
    ],
    'phpcs.phar'   => [
        'https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.5.4/phpcs.phar',
        '31311c5afb7c9fdde7196ef60f9a6d44'
    ],
    'phpcbf.phar'  => [
        'https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.5.4/phpcbf.phar',
        '3bbc4d4d026928ae5b77086b85cdc568'
    ]
];
foreach ($tools as $name => $data) {
    $file = __DIR__ . '/../tools/' . basename($name);
    if (file_exists($file) && md5_file($file) === $data[1]) {
        continue;
    }
    $temp = @file_get_contents($data[0]);
    if ($temp !== false && md5($temp) === $data[1]) {
        @file_put_contents($file, $temp);
    }
}
