#!/usr/bin/env php
<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

if (!defined('STORAGE_INTL')) {
    exit();
}

$files = scandir(STORAGE_INTL);
if (!$files) {
    $files = [];
}
foreach ($files as $file) {
    if (is_file(STORAGE_INTL . '/' . $file) && preg_match('(\.json$)i', $file)) {
        $data = @json_decode(file_get_contents(STORAGE_INTL . '/' . $file), true);
        if ($data) {
            file_put_contents(
                STORAGE_INTL . '/' . $file . '.php',
                '<?php $lang = ' . var_export($data, true) . ';'
            );
            if (function_exists('opcache_compile_file')) {
                try {
                    @opcache_compile_file(STORAGE_INTL . '/' . $file . '.php');
                } catch (\Exception $ignore) {
                }
            }
        }
    }
}
