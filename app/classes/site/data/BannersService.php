<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\files\FileStorageInterface;

class BannersService
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
        return $this->db->banners()
            ->filter('hidden', 0)
            ->filter('site', SITE);
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
    public function all(int $lang, int $limit = 5)
    {
        $query = $this->query()
            ->filter('lang', $lang)
            ->limit($limit);

        return array_map(function ($item) {
            return $this->normalize($item);
        }, $query->select());
    }
}
