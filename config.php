<?php
define('CLI', php_sapi_name() === 'cli');
define('BASEDIR', __DIR__);

define('VERSION', '2.2.0');
define('APPNAME', $config->get('APPNAME', basename(__DIR__)));
define('APPNAME_CLEAN', $config->get('APPNAME_CLEAN', strtoupper(preg_replace('([^a-z0-9_]+)', '_', APPNAME))));
define('TIMEZONE', $config->get('TIMEZONE', 'Europe/Sofia'));

// database
define(
    'DATABASE',
    $config->get('DATABASE', 'mysql://root@127.0.0.1/' . APPNAME . '?timezone=' . TIMEZONE . '&charset=utf8mb4')
);

define('DEBUG', $config->get('DEBUG', true));
define('SIGNATUREKEY', $config->get('SIGNATUREKEY', 'Place-a-random-signature-key-here'));

// disk locations
define('STORAGE_UPLOADS', $config->get('STORAGE_UPLOADS', __DIR__ . '/storage/uploads'));
define('STORAGE_CACHE', $config->get('STORAGE_CACHE', __DIR__ . '/storage/cache'));
define('STORAGE_TMP', $config->get('STORAGE_TMP', __DIR__ . '/storage/tmp'));
define('STORAGE_INTL', $config->get('STORAGE_INTL', __DIR__ . '/storage/intl'));

define('SMTPCONNECTION', $config->get('SMTPCONNECTION', null));

// public site url
define('PUBLIC_URL', $config->get('PUBLIC_URL', '/'));
define('MULTISITE', $config->get('MULTISITE', false));
define('ANALYTICSCONFIG', $config->get('ANALYTICSCONFIG', ''));
define('ANALYTICSURL', $config->get('ANALYTICSURL', ''));
define('ANALYTICSSCOPE', $config->get('ANALYTICSSCOPE', ''));
define('SMTPUSER', $config->get('SMTPUSER', ''));
define('SMTPPASS', $config->get('SMTPPASS', ''));
define('MAILFROM', $config->get('SMTPPASS', ''));
