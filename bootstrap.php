<?php
// composer autoloader
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/.env.php';

if (!isset($config)) {
    $config = new \vakata\config\Config();
    $config->set('CONFIGFILE', __DIR__ . '/.env');
    if ($config->get('CONFIGFILE')) {
        $config->fromFile($config->get('CONFIGFILE'));
    }
} else {
    $config = new \vakata\config\Config($config);
}

// include the config file
require_once __DIR__ . '/config.php';

// normalize REDIRECT_ vars
if (!CLI) {
    foreach ($_SERVER as $k => $v) {
        if (substr($k, 0, 9) === 'REDIRECT_' && !isset($_SERVER[substr($k, 9)])) {
            $_SERVER[substr($k, 9)] = $v;
        }
    }
}

// normalize cert number
if (isset($_SERVER['SSL_CLIENT_M_SERIAL'])) {
    $_SERVER['SSL_CLIENT_M_SERIAL'] = ltrim($_SERVER['SSL_CLIENT_M_SERIAL'], '0');
}

// timezone & locale
setlocale(LC_ALL, 'en_US.UTF-8');
date_default_timezone_set(defined('TIMEZONE') ? TIMEZONE : 'Europe/Sofia');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// normalize session
ini_set('session.use_cookies', true);
ini_set("session.entropy_file", "/dev/urandom");
ini_set("session.entropy_length", "32");
ini_set('session.session.hash_bits_per_character', 6);
ini_set('session.use_only_cookies', true);
ini_set('session.cookie_httponly', true);
ini_set('session.use_trans_sid', false);
ini_set('session.name', defined('APPNAME_CLEAN') && APPNAME_CLEAN ? APPNAME_CLEAN . '_SESSID' : 'PHPSESSIONID');
if (!(int)ini_get('session.gc_probability') || !(int)ini_get('session.gc_divisor')) {
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');
}

// error handling
error_reporting(E_ALL);
ini_set('log_errors', 'On');
ini_set('display_errors', ( defined('DEBUG') && DEBUG ? 'On' : 'Off' ));
ini_set('display_start_up_errors', ( defined('DEBUG') && DEBUG ? 'On' : 'Off' ));
ini_set('log_errors_max_len', 0);
ini_set('ignore_repeated_errors', 1);
ini_set('ignore_repeated_source', 0);
ini_set('track_errors', 0);
ini_set('html_errors', 0);
ini_set('report_memleaks', 1);
if (defined('DEBUG') && DEBUG) {
    ini_set('opcache.enable', 0);
}

// create a default exception handler
set_exception_handler(function ($e) {
    @error_log(
        date("[d-M-Y H:i:s e] ") .
        'PHP Exception:' .
        ((int)$e->getCode() ? ' ' . $e->getCode() . ' -' : '') . ' ' . $e->getMessage() .
        ' in ' . $e->getFile() . ' on line ' . $e->getLine()
    );
    while (ob_get_level() && ob_end_clean()) {
    }
    if (!headers_sent()) {
        header(
            'Content-Type: text/html; charset=utf-8',
            true,
            $e->getCode() >= 200 && $e->getCode() <= 503 ? $e->getCode() : 500
        );
    }
    echo '
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8"><title>Please, try again later.</title>
            <style>body { background:#e0e0e0; min-width:320px; }
                h1 { font-size:1.4em; text-align:center; margin:2em 0 0 0; color:#8b0000; text-shadow:1px 1px 0 white; }
                p { font-size:1.2em; text-align:center; margin:2em 0 0 0; }
            </style>
        </head>
        <body>
            <h1>Please, try again later.</h1>' .
            (
                DEBUG ?
                    '<p>
                        <strong>' . htmlspecialchars($e->getMessage()) . '</strong><br />
                        <code>' . htmlspecialchars($e->getFile() . ':' . $e->getLine()) . '</code>
                    </p>' :
                    ''
            ) .
        '</body>
    </html>';
    die();
});
// turn all errors into exceptions
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // do not touch errors where @ is used or that are not marked for reporting
    if ($errno === 0 || !($errno & error_reporting())) {
        return true;
    }
    // do not throw exceptions for "lightweight" errors - those will end up in the log and will not break execution
    if (in_array($errno, [ E_NOTICE, E_DEPRECATED, E_STRICT, E_USER_NOTICE, E_USER_DEPRECATED ])) {
        @error_log(
            date("[d-M-Y H:i:s e] ") .
            'PHP Notice: ' . $errno . ' ' . $errstr .
            ($errfile && $errline ? ' in '.$errfile.' on line '.$errline : '')
        );
        return true;
        // return false;
    }
    // throw exception for all others
    throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
});

//restrictions
$dirs = array_map(
    function ($v) {
        return rtrim(realpath($v), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    },
    array_filter(
        array_unique([
            sys_get_temp_dir(),
            ini_get('upload_tmp_dir'),
            STORAGE_UPLOADS,
            STORAGE_CACHE,
            STORAGE_TMP,
            STORAGE_INTL,
            __DIR__
        ])
    )
);
foreach ($dirs as $k => $v) {
    foreach ($dirs as $kk => $vv) {
        if ($k !== $kk && strpos($v, $vv) === 0) {
            unset($dirs[$k]);
            break;
        }
    }
}
ini_set('open_basedir', implode(
    PATH_SEPARATOR,
    $dirs
));
unset($dirs, $k, $kk, $v, $vv);

// remove revealing headers
if (!CLI) {
    @header_remove('x-powered-by');
}
