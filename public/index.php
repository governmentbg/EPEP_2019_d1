<?php
require_once __DIR__ . '/../bootstrap.php';

use vakata\http\Request;
use vakata\http\Response;
use vakata\http\Emitter;
use Zend\Diactoros\Response\Serializer as ResponseSerializer;
use Zend\Diactoros\Stream;
use mindplay\middleman\Dispatcher;
use League\Plates\Engine as Views;
use vakata\database\DB;
use vakata\di\DIContainer;
use vakata\files\FileDatabaseStorage;
use site\Intl;
use vakata\image\Image;
use vakata\image\ImageException;
use site\PageFactory;
use site\PageNotFoundException;
use vakata\cache\Filecache;
use vakata\mail\driver\SMTPSender;

define("CLASSES", [
    'page'          => "\\site\\controller\\Page",
    'homepage'      => "\\site\\controller\\Home",
    'news'          => "\\site\\controller\\News",
    'gallery'       => "\\site\\controller\\Gallery",
    'search'        => "\\site\\controller\\Search",
    'contacts'      => "\\site\\controller\\Contacts",
    'contactform'   => "\\site\\controller\\Contactform",
    'sitemap'       => "\\site\\controller\\Sitemap",
    'schedule'      => "\\site\\controller\\Schedule",
    'acts'          => "\\site\\controller\\Acts",
    'polls'         => "\\site\\controller\\Polls",
    'employees'     => "\\site\\controller\\Employees",
    'zop'           => "\\site\\controller\\Zop",
    'copy'          => "\\site\\controller\\Copy",
    'embed'         => "\\site\\controller\\Embed",
    'announcements' => "\\site\\controller\\Announcements"
]);

$dbc = new DB(DATABASE);

$cache = new Filecache(realpath(STORAGE_CACHE), APPNAME_CLEAN);
if ($cached = $cache->get(APPNAME_CLEAN . '_schema')) {
    $dbc->setSchema($cached);
} else {
    $cache->prepare(APPNAME_CLEAN . '_schema');
    $dbc->parseSchema();
    $cache->set(APPNAME_CLEAN . '_schema', $dbc->getSchema(), null, DEBUG ? '+10 minutes' : '+1 year');
}

if (CLI) {
    $req = Request::fromString(file_get_contents('php://stdin'));
    $url = $req->getUrl()->setBasePath('/');
} else {
    $req = Request::fromGlobals();
    $url = $req->getUrl();
}

$site = $dbc->one("SELECT s.* FROM sites s WHERE s.disabled = 0 AND s.dflt = 1");
if (MULTISITE) {
    $site = $dbc->one(
        "SELECT s.* FROM sites s, site_domain d WHERE s.disabled = 0 AND s.site = d.site AND d.domain = ?",
        $url->getHost()
    ) ?? $site;
}

if ($site === null) {
    throw new \Exception("Invalid config", 404);
}
define('SITE', $site['site']);
define('HOMEPAGE', $site['tree']);
define('LANGUAGES', $dbc->all(
    "SELECT l.code, l.lang FROM languages l, site_lang s WHERE l.lang = s.lang AND s.site = ?",
    $site['site'],
    'code',
    true
));

$files = new FileDatabaseStorage(realpath(STORAGE_UPLOADS), $dbc, 'uploads');
$view = new Views(realpath(__DIR__ . '/../app/views/'));
$view->addData([
    'url' => $url,
    'req' => $req,
    'current' => $url->self(),
    'content' => function ($content) {
        return str_replace('/admin/public/upload/', '/public/public/upload/', $content);
    }
]);
$sender = new SMTPSender(SMTPCONNECTION, SMTPUSER, SMTPPASS);
$res = (new Dispatcher([
    function (Request $req, $next) {
        $res = $next($req);
        if ($res->hasHeader('Location') && $res->getStatusCode() === 200) {
            $res = $res->withStatus(303);
        }
        return $res;
    },
    function (Request $req, $next) {
        $res = $next($req)
            ->withHeader('X-UA-Compatible', 'IE=edge')
            ->withHeader('X-Frame-Options', 'sameorigin')
            ->withHeader('X-XSS-Protection', '1');
        $contentType = $res->getHeaderLine('Content-Type');
        if (strpos($contentType, 'jscript') !== false ||
            strpos($contentType, 'javascript') !== false ||
            strpos($contentType, 'ecmascript') !== false ||
            strpos($contentType, 'text/css') !== false
        ) {
            $res = $res->withHeader('X-Content-Type-Options', 'nosniff');
        }
        return $res;
    },
    function (Request $req, $next) use ($files, $url) {
        $slug = 'upload';
        if ($url->getSegment(0) === $slug) {
            if (($req->getMethod() === 'GET' || $req->getMethod() === 'HEAD') && (int)$url->getSegment(1)) {
                $file = $files->get((int)$url->getSegment(1), true);
                if ($file['name'] !== $url->getSegment(2)) {
                    return (new Response())->withStatus(404);
                }
                $replace = null;
                if (($req->getQueryParams()['w'] ?? 0) || ($req->getQueryParams()['h'] ?? 0)) {
                    $name = 'thumb_' .
                            md5($file['path']) . '_' .
                            $req->getQuery('w', '0', 'int') . 'x' . $req->getQuery('w', '0', 'int');
                    if (is_file(STORAGE_TMP . '/' . $name)) {
                        $replace = file_get_contents(STORAGE_TMP . '/' . $name);
                    } else {
                        try {
                            $replace = Image::fromPath($file['path'])
                                ->crop(
                                    min(4096, (int)($req->getQueryParams()['w'] ?? 0)),
                                    min(4096, (int)($req->getQueryParams()['h'] ?? 0)),
                                    is_array($file['settings']) && isset($file['settings']['thumbnail']) ?
                                        $file['settings']['thumbnail'] : []
                                )
                                ->toString();
                            file_put_contents(STORAGE_TMP . '/' . $name, $replace);
                        } catch (ImageException $ignore) {
                        }
                    }
                }
                $name = $file['name'];
                $extension = substr($name, strrpos($name, '.') + 1);
                if (!$req->getQuery('inline')) {
                    $disposition = !$req->getQuery('download') && in_array(
                        strtolower($extension),
                        ['txt','png','jpg','gif','jpeg','html','htm','mp3','mp4']
                    ) ? 'inline' : 'attachment';
                } else {
                    $disposition = 'inline';
                }
                $res = (new Response(200, null, [
                    'Last-Modified' => gmdate('D, d M Y H:i:s', $replace ? time() : filemtime($file['path'])).' GMT',
                    'ETag' => $replace ? md5($replace) : $file['hash'],
                    'Cache-Control' => 'public',
                    'Pragma' => 'public',
                    'Expires' => gmdate('D, d M Y H:i:s', time() + 24 * 3600).' GMT',
                    'Content-Disposition' => $disposition.'; '.
                            'filename="'.preg_replace('([^a-z0-9.-]+)i', '_', $name).'"; '.
                            'filename*=UTF-8\'\''.rawurlencode($name).'; '.
                            'size='.($replace ? strlen($replace) : (string)$file['size'])
                ]));
                $res = $res->setContentTypeByExtension($extension);
                if ($replace) {
                    $res = $res
                        ->withHeader('Content-Length', strlen($replace))
                        ->setBody($replace);
                } else {
                    $res = $res
                        ->withHeader('Content-Length', $file['size'])
                        ->withBody(new Stream(fopen($file['path'], 'r')));
                }
                return $res;
            }
        }
        return $next($req);
    },
    function (Request $req, callable $next) use ($dbc, $url, $view, $files, $cache, $sender) {
        try {
            if ($url->getSegment(0) === 'preview') {
                $fact = new PageFactory($dbc);
                $page = $fact->getPageFromToken($url->getSegment(1));
                $intl = new Intl();
                if (isset(LANGUAGES[$page->getLanguageCode()]) &&
                    is_file(STORAGE_INTL . '/public_' . $page->getLanguageCode() . '.json')
                ) {
                    $intl->addLanguage(
                        $page->getLanguageCode(),
                        STORAGE_INTL . '/public_' . $page->getLanguageCode() . '.json'
                    );
                }
                $view->addData([ 'intl' => $intl ]);
                $view->addData([ 'translations' => $page->getTranslations() ]);
                $di = new DIContainer();
                $di
                    ->register($di)
                    ->register($dbc)
                    ->register($req)
                    ->register($url)
                    ->register($view)
                    ->register($cache)
                    ->register($files, 'file')
                    ->register($files, 'vakata\\files\\FileStorageInterface')
                    ->register($sender);
                $controller = $di->instance(CLASSES[ strlen($page->template) ? $page->template : 'page' ]);
                return $controller($page, $url, $req, new Response());
            }
        } catch (PageNotFoundException $e) {
            return (new Response())->setBody('Page not found')->withStatus(404);
        }
        return $next($req);
    },
    function (Request $req, callable $next) use ($dbc, $url, $view, $files, $cache, $sender) {
        try {
            $fact = new PageFactory($dbc);
            $path = trim((string)$url->getRealPath(), "/");
            if (preg_match('/^google[a-z0-9]{16}\.html/', $path)) {
                $verifications = [ 'google69c0d5d2f23efb0e.html' ];
                if (in_array($path, $verifications)) {
                    return (new Response())
                        ->setBody('google-site-verification: ' . $path);
                }
                return (new Response())
                    ->withStatus(404)
                    ->setBody('File not found');
            }
            $rurl = $dbc->one("SELECT * FROM redirects WHERE url_from = ? AND site = ?", [ $path, SITE ]);
            if ($rurl !== null) {
                switch ($rurl['rtype']) {
                    case 'none':
                        $path = $rurl['url_to'];
                        break;
                    case 'temporary':
                        return (new Response())
                            ->withStatus(307)
                            ->withHeader('Location', $url->get($rurl['url_to']));
                    case 'permanent':
                        return (new Response())
                            ->withStatus(301)
                            ->withHeader('Location', $url->get($rurl['url_to']));
                }
            }
            $page = $fact->getPageByUrl($path);
            $intl = new Intl();
            if (isset(LANGUAGES[$page->getLanguageCode()]) &&
                is_file(STORAGE_INTL . '/public_' . $page->getLanguageCode() . '.json')
            ) {
                $intl->addLanguage(
                    $page->getLanguageCode(),
                    STORAGE_INTL . '/public_' . $page->getLanguageCode() . '.json'
                );
            }
            $view->addData([ 'intl' => $intl ]);
            if ($page->isHidden()) {
                throw new PageNotFoundException();
            }
            if (strlen($page->redirect)) {
                return (new Response())->withHeader('Location', $url->get($page->redirect));
            }
            $view->addData([ 'translations' => $page->getTranslations() ]);
            $di = new DIContainer();
            $di
                ->register($di)
                ->register($dbc)
                ->register($req)
                ->register($url)
                ->register($view)
                ->register($cache)
                ->register($files, 'file')
                ->register($files, 'vakata\\files\\FileStorageInterface')
                ->register($sender);
            $controller = $di->instance(CLASSES[ strlen($page->template) ? $page->template : 'page' ]);
            return $controller($page, $url, $req, new Response());
        } catch (PageNotFoundException $e) {
            $intl = new Intl();
            $fact = new PageFactory($dbc);
            $banners = new site\data\BannersService($dbc, $files);
            $lang = isset(LANGUAGES[$url->getSegment(0)]) ? $url->getSegment(0) : 'bg';
            $langId = LANGUAGES[$lang] ?? 1;
            if (is_file(STORAGE_INTL . '/public_' . $lang . '.json')) {
                $intl->addLanguage(
                    $lang,
                    STORAGE_INTL . '/public_' . $lang . '.json'
                );
            }
            $view->addData([ 'intl' => $intl ]);
            $site = $fact->site($langId);

            return (new Response())
                ->withStatus(404)
                ->setBody($view->render('_404', [
                    'menu'          => $fact->getTopMenu($langId),
                    'homepage'      => $fact->getHomepage($langId),
                    'site'          => $site,
                    'lang'          => $lang,
                    'footer'        => [
                        'footer'    => $fact->footer($langId),
                        'banners'   => $banners->all((int) $langId),
                        'site'      => $site
                    ]
                ]));
        }
        catch (\Exception $e) {
            return (new Response())->withStatus(500)->setBody('Internal Server Error');
        }
    }
]))->dispatch($req);

if ($req->getMethod() === 'HEAD' && $res instanceof Response) {
    $res = $res->setBody('');
}

// send the response
if (CLI) {
    // One could just echo out $res->getBody() if no header info is needed
    echo ResponseSerializer::toString($res);
    exit($res->getStatusCode() >= 400 ? 1 : 0);
} else {
    if ($res instanceof Response) {
        (new Emitter())->emit($res);
    }
}
