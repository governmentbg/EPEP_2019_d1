<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\files\FileStorageInterface;
use vakata\cache\CacheInterface;
use vakata\collection\Collection;

class SearchService
{
    protected $db;

    public function __construct(DBInterface $db)
    {
        $this->db = $db;
    }
    public function search(string $q, string $category, int $page = 1, int $perpage = 10)
    {
        $query = $this->db->search_index()
            ->where('MATCH (title, data) AGAINST (?)', [$q])
            ->paginate($page, $perpage);
        if (MULTISITE) {
            $query->filter('site', SITE);
        }
        if (in_array($category, $this->getCategories())) {
            $query->filter('module', $category);
        }
        return [
            'items' => Collection::from($query->select(['url', 'title', 'data']))
                ->map(function ($v) use ($q) {
                    $v['data'] = $this->highlight($v['data'], $q);
                    return (object)$v;
                })
                ->toArray(),
            'count' => $query->count()
        ];
    }
    protected function highlight(string $text, string $words, int $max = 200)
    {
        $words = preg_replace_callback('/"[^"]+"/iu', function ($matches) {
            return preg_replace('/\s+/iu', "\x05", $matches[0]);
        }, $words);

        $words = preg_split('/\s/iu', $words, -1, PREG_SPLIT_NO_EMPTY);
        $text = trim(preg_replace('/([\n\r\s\t]+|(&nbsp;)+)/iu', ' ', $text));
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
            return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
        }, $text);

        $rpl = [];
        foreach ($words as $k => $v) {
            $v = preg_replace('/[^a-zA-Zа-яА-Я0-9\x05]/iu', '', $v);
            if ($v == '&') {
                $words[$k] = '/&([^#\w])/iu';
                $rpl[$k] = "\x05&\x06$1";
            } elseif ($v == '') {
                unset($words[$k]);
            } else {
                $words[$k] = '/' . preg_replace('/\x05+/iu', '\W+', preg_quote($v, '/')) . '/iu';
                $rpl[$k] = "\x05$0\x06";
            }
        }
        $highlighted = preg_filter($words, $rpl, $text);
        if (!empty($highlighted)) {
            $text = $highlighted;
        } else {
            return mb_substr($text, 0, $max == 0 ? null : $max);
        }

        $highlighted = preg_filter('/\x06(\W*)\x05/iu', '$1', $text);
        if (!empty($highlighted)) {
            $text = $highlighted;
        }

        preg_match_all('/\x05(.*?)\x06/iu', $text, $m);
        $longest = '';
        if (is_array($m) && isset($m[1]) && count($m[1])) {
            foreach ($m[1] as $k => $v) {
                if (mb_strlen($v) > mb_strlen($longest)) {
                    $longest = $v;
                }
            }
        }

        if ($max > 0) {
            $start = mb_strpos($text, $longest)-1;
            $end = mb_strlen($longest)+2;
            if (mb_strlen($longest) <= $max) {
                $before = floor(($max - mb_strlen($longest)) / 2);
                $after = $before;
                if ($before > $start) {
                    $after = $after + $before - $start;
                    $before = $start;
                }
                $start = $start - $before;
                $end = $end + $before + $after;
            }
            if ($start > 0) {
                $bf = mb_substr($text, 0, $start);
                $af = mb_substr($text, $start);
                $bf = mb_strlen($bf) - mb_strrpos($bf, ' ') - 1;
                $af = mb_strpos($af, ' ');
                if ($af != 0 && $bf != 0) {
                    if ($bf <= $af) {
                        $start -= $bf;
                    } else {
                        $start += $af;
                    }
                    if ($start < 0) {
                        $start = 0;
                    }
                }
            }
            $text = mb_substr($text, $start, $end);
            if (mb_strpos($text, "\x06") < mb_strpos($text, "\x05")) {
                $text = "&hellip;\x05".$text;
            } elseif ($start > 0) {
                $text = "&hellip;".$text;
            }
            if (mb_strrpos($text, "\x06") < mb_strrpos($text, "\x05")) {
                $text = $text."\x06&hellip;";
            } else {
                $text = $text."&hellip;";
            }
        }

        $highlighted = htmlentities($highlighted, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $highlighted = preg_filter(['/\x05/', '/\x06/'], ['<b class="highlight">','</b>'], $text);
        if (!empty($highlighted)) {
            $text = $highlighted;
        }

        return $text;
    }
    public function getCategories()
    {
        return [ 'news', 'contacts', 'court', 'zop' ];
    }
}
