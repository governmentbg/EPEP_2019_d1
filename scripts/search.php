#!/usr/bin/env php
<?php
/**
 * A script to generate a search index
 */

require_once __DIR__ . '/../bootstrap.php';

@ob_end_flush();
@ob_implicit_flush();

if (!defined('DATABASE')) {
    echo 'Database config not found!' . "\r\n";
    exit(1);
}

$base = isset($argv[1]) && strpos($argv[1], '://') !== false ? $argv[1] : 'http://localhost/'; // the main URL
$host = explode('/', explode('://', $base, 2)[1], 2)[0]; // the host
$index = 'search_index'; // the main URL

if (!$base) {
    echo "Missing configuration!\r\n";
    die();
}

$dbc = new \vakata\database\DB(DATABASE);

$site = MULTISITE ? $dbc->one("SELECT site FROM site_domain WHERE domain = ?", [$host]) : null;

// custom downloader
$fetch = function (string $url) use (&$fetch) {
    $command = 'php '  . realpath(__DIR__ . '/../public/public/index.php');
    $hst = 'localhost';
    if (strpos($url, '://') !== false) {
        $hst = explode('/', explode('://', $url, 2)[1], 2)[0];
        $url = '/' . explode('/', explode('://', $url, 2)[1], 2)[1];
    }
    ob_start();
    $res = 0;
    passthru('echo "GET '.$url.' HTTP/1.1'."\n".'Host: '.$hst.'" | ' . $command, $res);
    $data = ob_get_contents();
    ob_end_clean();
    if ($res !== 0) {
        throw new \Exception();
    }
    $break = strpos($data, "\r\n\r\n") === false ? "\n" : "\r\n"; // just in case someone breaks RFC 2616
    list($headers, $message) = array_pad(explode($break . $break, $data, 2), 2, '');
    $headers = explode($break, preg_replace("(" . $break . "\s+)", " ", $headers));
    $status = 200;
    if (isset($headers[0]) && strlen($headers[0])) {
        $temp = explode(' ', $headers[0]);
        if (in_array($temp[0], ['HTTP/1.1', 'HTTP/2.0', 'HTTP/1.0'])) {
            $status = (int)$temp[1];
            unset($headers[0]);
            $headers = array_values($headers);
        }
    }
    $temp = array_filter($headers);
    $headers = [];
    foreach ($temp as $v) {
        $v = explode(':', $v, 2);
        $name = trim($v[0]);
        $name = str_replace('_', ' ', strtolower($name));
        $name = str_replace('-', ' ', strtolower($name));
        $name = str_replace(' ', '-', ucwords($name));
        $headers[$name] = trim($v[1]);
    }
    if ($status && $status >= 400) {
        throw new \Exception();
    }
    if (isset($headers['Location'])) {
        return $fetch($headers['Location']);
    }
    return $message;
};
$indxr = function (string $url, string $data) use ($dbc, $base, $index, $site) {
    $data = @json_decode(explode('</script>', explode('<script type="index/json">', $data, 2)[1], 2)[0], true);
    if ($data) {
        $dbc->query(
            "REPLACE INTO " . $index . " (si, url, module, title, data, meta, indexed, remove, site) VALUES (??)",
            [
                md5($url),
                str_replace($base, '', $url),
                $data['module'] ?? '',
                $data['title'] ?? $url,
                $data['data'] ?? '',
                json_encode($data),
                date('Y-m-d H:i:s'),
                0,
                $site
            ]
        );
    }
};

if ($site) {
    $dbc->query("UPDATE " . $index . " SET remove = 1 AND site = ?", [$site]);
} else {
    $dbc->query("UPDATE " . $index . " SET remove = 1");
}

\helpers\Indexer::get($base, $fetch, $indxr)
    ->filter(function ($url) {
        $file = array_reverse(explode('/', explode('?', $url)[0]))[0] ?? '';
        $ext  = strpos($file, '.') === false ? 'html' : array_reverse(explode('.', $file))[0] ?? '';
        return in_array($ext, ['htm','html']);
    })
    ->index();
if ($site) {
    $dbc->query("DELETE FROM " . $index . " WHERE remove = 1 AND site = ?", [$site]);
} else {
    $dbc->query("DELETE FROM " . $index . " WHERE remove = 1");
}
