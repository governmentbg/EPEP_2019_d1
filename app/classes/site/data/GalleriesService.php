<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\files\FileStorageInterface;
use vakata\cache\CacheInterface;
use vakata\collection\Collection;

class GalleriesService
{
    protected $db;
    protected $files;
    protected $cache;

    public function __construct(DBInterface $db, FileStorageInterface $files, CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->files = $files;
        $this->cache = $cache;
    }

    // this is the base query
    protected function query()
    {
        $base = $this->db->galleries()
            ->filter('_status', 'published')
            ->filter('hidden', 0)
            ->filter('visible_beg', ['lte' => date('Y-m-d H:i:s')])
            ->where('galleries.visible_end > ? OR galleries.visible_end IS NULL', [date('Y-m-d H:i:s')]);
        if (MULTISITE) {
            $base->filter('site', SITE);
        }
        return $base;
    }
    protected function normalize(array $v)
    {
        $v = (object)$v;
        $v->images = array_filter(explode(',', $v->images));
        foreach ($v->images as $k => $vv) {
            try {
                $file = $this->files->get($vv);
                $v->images[$k] = [
                    'url' => $file['id'] . '/' . $file['name'],
                    'settings' => $file['settings']
                ];
            } catch (\Exception $e) {
                $v->images[$k] = null;
            }
        }
        $v->images = array_filter($v->images);
        $v->image = count($v->images) ? current($v->images) : null;
        return $v;
    }
    public function top(int $lang, int $limit = 5): array
    {
        return Collection::from(
            $this->query()
                    ->filter('lang', $lang)
                    ->sort('fordate', true)
                    ->limit($limit)
                    ->select()
        )
            ->map(function ($v) {
                return $this->normalize($v);
            })
            ->toArray();
    }
    public function single(int $lang, int $id)
    {
        $gallery = $this->query()
            ->filter('lang', $lang)
            ->filter('gallery', $id)
            ->with('tags')
            ->limit(1)[0] ?? null;
        return $gallery !== null ? $this->normalize($gallery) : null;
    }
    public function listing(int $lang, array $tags = [], int $page = 1, int $perpage = 10)
    {
        $tags = array_filter(array_unique(array_values($tags)));
        $query = $this->query()
            ->filter('lang', $lang)
            ->with('tags')
            ->sort('fordate', true)
            ->paginate($page, $perpage);
        if (count($tags)) {
            $query->filter('tags.tag', $tags);
        }
        return [
            'galleries' => Collection::from($query->select())
                ->map(function ($v) {
                    return $this->normalize($v);
                })
                ->toArray(),
            'count' => $query->count()
        ];
    }
}
