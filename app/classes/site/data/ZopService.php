<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\files\FileStorageInterface;
use vakata\collection\Collection;

class ZopService
{
    protected $db;
    protected $files;

    public function __construct(DBInterface $db, FileStorageInterface $files)
    {
        $this->db = $db;
        $this->files = $files;
    }
    protected function query(int $category)
    {
        return $this->db->zop()
            ->filter('site', SITE)
            ->filter('hidden', 0)
            ->filter('category', $category);
    }
    protected function normalize(array $v)
    {
        $v = (object)$v;
        $files = array_filter(explode(',', $v->files));
        $v->files = [];

        foreach ($files as $file) {
            try {
                $file = $this->files->get($file);
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
        return $v;
    }
    public function categories()
    {
        return [
            1 => 'ВЪТРЕШНИ ПРАВИЛА',
            'ПУБЛИЧНИ ПОКАНИ',
            'ПРОЦЕДУРИ',
            'ПРЕДВАРИТЕЛНИ ОБЯВЛЕНИЯ',
            'ОБЯВИ',
            'СТАНОВИЩА НА АОП'
        ];
    }
    public function single(int $id, int $category)
    {
        $zop = $this->query($category)
            ->filter('zop', $id)
            ->limit(1)[0] ?? null;
        return $zop !== null ? $this->normalize($zop) : null;
    }
    public function listing(int $category, int $page = 1, int $perpage = 10)
    {
        $query = $this->query($category)
            ->sort('created', true)
            ->paginate($page, $perpage);
        return [
            'zops' => Collection::from($query->select())
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
