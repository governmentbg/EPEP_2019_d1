<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\phptree\Tree;
use vakata\phptree\Node;

class SitemapService
{
    protected $db;

    public function __construct(DBInterface $db)
    {
        $this->db = $db;
    }
    public function get(int $lang)
    {
        $tree = new Tree($this->db, "tree_struct", [
            'id'       => 'id',
            'parent'   => 'pid',
            'position' => 'pos',
            'level'    => 'lvl',
            'left'     => 'lft',
            'right'    => 'rgt'
        ], HOMEPAGE);
        $root = $this->db->one('SELECT tree FROM sites WHERE site = ?', [ SITE ]);
        $root = $tree->getNode((int) $root);

        $data = $this->db->all(
            "SELECT
                tree_data.id,
                tree_data.title,
                tree_struct.lvl,
                tree_data.settings
            FROM
                tree_data, tree_struct
            WHERE
                tree_struct.id = tree_data.id AND
                tree_data.published = 1 AND
                tree_data.hidden = 0 AND
                tree_data.lang = ? AND
                tree_struct.pid IS NOT NULL AND
                tree_struct.lft >= ? AND
                tree_struct.rgt <= ?
            ORDER BY tree_struct.lft",
            [ $lang, $root->lft, $root->rgt ],
            'id',
            true
        );
        foreach ($data as $key => $value) {
            $settings = json_decode($value['settings'], true) ?? [];
            if (isset($settings['redirect']) && strlen($settings['redirect'])) {
                $data[$key]['url'] = $settings['redirect'];
            } elseif (isset($settings['url']) && strlen($settings['url'])) {
                $data[$key]['url'] = trim($settings['url'], '/*');
            } else {
                $data[$key]['url'] = $lang . '/' . $key;
            }
            $inMenu = (int) ($settings['menu'] ?? 1);
            if (!$inMenu) {
                unset($data[$key]);
            }
        }

        return [ 'data' => $data, 'root' => $root ];
    }
}
