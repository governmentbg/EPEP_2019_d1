<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\files\FileStorageInterface;
use vakata\collection\Collection;

class NewsService
{
    protected $db;
    protected $files;
    protected $gallery;

    public function __construct(DBInterface $db, FileStorageInterface $files, GalleriesService $gallery)
    {
        $this->db = $db;
        $this->files = $files;
        $this->gallery = $gallery;
    }

    // this is the base query
    protected function query()
    {
        $base = $this->db->news()
            ->filter('_status', 'published')
            ->filter('hidden', 0)
            ->filter('visible_beg', ['lte' => date('Y-m-d H:i:s')])
            ->where('news.visible_end > ? OR news.visible_end IS NULL', [date('Y-m-d H:i:s')]);
        if (MULTISITE) {
            $base->filter('site', SITE);
        }
        return $base;
    }
    protected function normalize(array $v, bool $deep = false)
    {
        $v = (object)$v;
        try {
            if (!(int)$v->image) {
                throw new \Exception('No image');
            }
            $file = $this->files->get($v->image);
            $v->image = $file['id'] . '/' . $file['name'];
        } catch (\Exception $e) {
            $v->image = null;
        }
        if ($deep) {
            if ((int) $v->gallery) {
                $v->gallery = $this->gallery->single((int) $v->lang, (int) $v->gallery);
            }
            if (strlen($v->files)) {
                $files = explode(',', $v->files);
                $v->files = [];
                $files = array_filter($files);
                foreach ($files as $file) {
                    try {
                        $file = $this->files->get((int) $file);
                        $file['url'] = $file['id'] . '/' . $file['name'];
                        $ext = explode('.', $file['name']);
                        $file['ext'] = end($ext);
                        $file['name'] = isset($file['settings']['name']) && strlen($file['settings']['name']) ?
                            $file['settings']['name'] :
                            $file['name'];
                        $file['size'] = $this->formatSizeUnits((int) $file['size']);
                        $v->files[] = $file;
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $v->files = [];
            }
            $related = $this->db->all('SELECT related FROM news_related WHERE news = ?', [ (int) $v->news ]);
            $v->related = [];
            if (count($related)) {
                $v->related = array_map(function ($item) {
                    return $this->normalize($item);
                }, $this->query()->filter('news.news', array_unique($related))->select());
            }
        }
        return $v;
    }
    public function top(string $lang, int $limit = 5): array
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
    public function single(string $lang, int $id)
    {
        $news = $this->query()
            ->filter('lang', $lang)
            ->filter('news', $id)
            ->with('tags')
            ->limit(1)[0] ?? null;

        return $news !== null ? $this->normalize($news, true) : null;
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
            'news' => Collection::from($query->select())
                ->map(function ($v) {
                    return $this->normalize($v);
                })
                ->toArray(),
            'count' => $query->count()
        ];
    }
    protected function formatSizeUnits($bytes)
    {
        if ($bytes == 0) {
            return null;
        }
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }
}
