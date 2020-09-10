<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\files\FileStorageInterface;

class ServicesService
{
    protected $db;
    protected $files;

    public function __construct(DBInterface $db, FileStorageInterface $files)
    {
        $this->db = $db;
        $this->files = $files;
    }
    protected function query()
    {
        $base = $this->db->services()
            ->filter('hidden', 0);
        if (MULTISITE) {
            $base->filter('site', SITE);
        }
        return $base;
    }
    protected function normalize(array $v)
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
        return $v;
    }
    public function all(string $lang, int $perpage = 10)
    {
        $query = $this->query()
            ->filter('lang', $lang)
            ->paginate(1, $perpage);

        return array_map(function ($item) {
            return $this->normalize($item);
        }, $query->select());
    }
}
