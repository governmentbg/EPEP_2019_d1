<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\files\FileStorageInterface;

class EmployeesService
{
    protected $db;
    protected $files;

    public function __construct(DBInterface $db, FileStorageInterface $files)
    {
        $this->db = $db;
        $this->files = $files;
    }
    protected function normalize(array $item)
    {
        $item = (object) $item;
        if ((int) $item->image) {
            try {
                $image = $this->files->get((int) $item->image);
                $item->image = $image['id'] . '/' . $image['name'];
            } catch (\Exception $e) {
                $item->image = null;
            }
        } else {
            $item->image = null;
        }

        $item->description = array_filter(explode("\n", $item->description));

        return $item;
    }
    public function employees(array $employees): array
    {
        if (!count($employees)) {
            return [];
        }
        $query = $this->db->employees()->filter('site', SITE)->filter('employee', $employees);

        $order = array_map(function ($item) {
            return 'employee = ' . $item . ' DESC';
        }, $employees);
        $query->order(implode(', ', $order));

        return array_map(function ($item) {
            return $this->normalize($item);
        }, $query->select());
    }
}
