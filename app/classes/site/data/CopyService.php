<?php

namespace site\data;

use vakata\database\DBInterface;

class CopyService
{
    protected $db;

    public function __construct(DBInterface $db)
    {
        $this->db = $db;
    }
    public function template(int $id, int $lang)
    {
        return $this->db->one(
            'SELECT template FROM tree_data WHERE id = ? AND lang = ? AND published = ?',
            [ $id, $lang, 1 ]
        );
    }
}
