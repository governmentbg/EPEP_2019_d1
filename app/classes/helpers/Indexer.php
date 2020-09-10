<?php

namespace helpers;

class Indexer
{
    protected $url;
    protected $fetch = null;
    protected $indexer = null;
    protected $queue = [];
    protected $filters = [];
    protected $processed = [];

    public function __construct(string $url, callable $fetch = null, callable $indexer = null)
    {
        $this->url = $url; // normalize?
        $this->fetch = $fetch ?? function (string $url) {
            $data = @file_get_contents(html_entity_decode($url));
            if ($data === false) {
                throw new \Exception();
            }
            return $data;
        };
        $this->indexer = $indexer ?? function ($url, $data) {
        };
        $this->add($url);
    }
    public static function get(string $url, callable $fetch = null, callable $indexer = null) : Indexer
    {
        return new self($url, $fetch, $indexer);
    }
    public function filter(callable $func) : Indexer
    {
        $this->filters[] = $func;
        return $this;
    }
    public function add(string $url) : Indexer
    {
        $this->queue[] = $url;
        $this->processed[$url] = true;
        return $this;
    }
    public function index()
    {
        while ($url = array_shift($this->queue)) {
            try {
                $data = call_user_func($this->fetch, $url);
            } catch (\Exception $e) {
                continue;
            }
            //echo $url;
            if ($this->shouldSearch($url)) {
                //echo ' S' . "\n";
                $matches = [];
                if (preg_match_all('((href=)(\'|")?([^ \'"\\)]+)(\'|"|\\)))i', $data, $matches)) {
                    foreach ($matches[3] as $k => $match) {
                        if ($match === '#' ||
                            strpos($match, 'tel:') === 0 ||
                            strpos($match, 'data:') === 0 ||
                            strpos($match, 'mailto:') === 0 ||
                            strpos($match, 'javascript:') === 0
                        ) {
                            continue;
                        }
                        $matchUrl = $this->normalizeUrl($match, $url);
                        //echo ' ' . $matchUrl;
                        if (!isset($this->processed[$matchUrl]) &&
                            $this->shouldDownload($matchUrl)
                        ) {
                            //echo ' D';
                            $this->queue[] = $matchUrl;
                            $this->processed[$matchUrl] = true;
                        }
                        //echo "\r\n";
                    }
                }
            }
            call_user_func($this->indexer, $url, $data);
        }
    }

    protected function shouldSearch(string $url) : bool
    {
        if (strpos($url, 'cdn-cgi/') !== false) {
            return false;
        }
        $file = array_reverse(explode('/', explode('?', $url)[0]))[0] ?? '';
        $ext  = strpos($file, '.') === false ? 'html' : array_reverse(explode('.', $file))[0] ?? '';
        return in_array($ext, ['htm','html']);
    }
    protected function shouldDownload(string $url) : bool
    {
        if (strpos($url, 'cdn-cgi/') !== false) {
            return false;
        }
        if (strpos($url, $this->url) !== 0) {
            return false;
        }
        foreach ($this->filters as $filter) {
            if (!call_user_func($filter, $url)) {
                return false;
            }
        }
        return true;
    }

    protected function normalizeUrl(string $url, string $currentUrl = null) : string
    {
        $data = parse_url($this->url);
        if ($url === 'p+') {
            return $url;
        }
        if ($url[0] === '?') {
            return explode('?', $currentUrl)[0] . $url;
        }
        if (substr($url, 0, 2) === '//') {
            return $url;
        }
        if ($url[0] === '/') {
            return ($data['scheme'] ?? 'http') . '://' . ($data['host'] ?? 'localhost') . $url;
        }
        if (strpos($url, '//') === false && $currentUrl) {
            $currentUrl = explode('/', substr($currentUrl, strlen($this->url)));
            unset($currentUrl[count($currentUrl) - 1]);
            $segments = explode('/', ltrim(preg_replace('(^\\./)', '', $url), '/'));
            foreach ($segments as $k => $segment) {
                if ($segment === '..') {
                    if (!count($currentUrl)) {
                        return $url;
                    }
                    unset($currentUrl[count($currentUrl) - 1]);
                    unset($segments[$k]);
                }
            }
            return $this->url . implode('/', array_filter(array_merge($currentUrl, $segments)));
        }
        return $url;
    }
}
