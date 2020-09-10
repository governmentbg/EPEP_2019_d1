<?php
namespace site;

use vakata\database\DBInterface;
use vakata\phptree\Tree;

class PageFactory
{
    protected $db;
    protected $tree;
    protected $lang;
    protected $pages = [];

    public function __construct(DBInterface $db)
    {
        $this->db = $db;
        $this->tree = Tree::fromDatabase($db, "tree_struct", [
            'id'       => 'id',
            'parent'   => 'pid',
            'position' => 'pos',
            'level'    => 'lvl',
            'left'     => 'lft',
            'right'    => 'rgt'
        ], HOMEPAGE);
        $this->lang = LANGUAGES;
    }

    public function getLanguages(bool $codeFirst = true)
    {
        return $codeFirst ? $this->lang : array_flip($this->lang);
    }
    public function langCodeToID(string $lang)
    {
        return $this->lang[$lang] ?? null;
    }
    public function langIDToCode(int $lang)
    {
        $temp = array_search($lang, $this->lang);
        return $temp !== false ? $temp : null;
    }
    protected function normalizeData(array $data): array
    {
        foreach ($data as $i => $page) {
            $settings = @json_decode($data[$i]['settings'], true);
            if (!$settings) {
                $settings = [];
            }
            $data[$i] = array_merge($settings, $page);
            unset($data[$i]['settings']);
            $content = @json_decode($data[$i]['content'], true);
            if (!$content) {
                $content = [];
            }
            $content = $content[$data[$i]['template']] ?? [];
            $data[$i]['content'] = $content;
        }
        return $data;
    }

    public function getMenu(int $lang, string $type = 'top'): array
    {
        $type = in_array($type, ['top', 'lft', 'rgt']) ? 'menu_'.$type : 'menu_top';
        $data = $this->db->all(
            'SELECT d.*
             FROM tree_data d, tree_struct s
             WHERE 
                d.hidden = 0 AND d.published = 1 AND d.'.$type.' = 1 AND d.lang = ? AND d.id = s.id AND 
                s.lft >= ? AND s.rgt <= ?
             ORDER BY s.lft',
            [ $lang, $this->tree->getNode(HOMEPAGE)->lft, $this->tree->getNode(HOMEPAGE)->rgt ]
        );
        $data = $this->normalizeData($data);
        return array_map(function ($v) {
            return new Page($this, $v);
        }, $data);
    }
    public function getPage(int $id, int $lang): Page
    {
        if (isset($this->pages[$lang . '/' . $id])) {
            return $this->pages[$lang . '/' . $id];
        }
        $node = $this->tree->getNode($id);
        if (!$node) {
            throw new PageNotFoundException();
        }
        $temp = array_values(
            array_map(
                function ($node) {
                    return $node->id;
                },
                $node->getAncestors()
            )
        );
        $ancestors = [];
        if ($id !== HOMEPAGE) {
            foreach ($temp as $v) {
                $ancestors[] = $v;
                if ($v === HOMEPAGE) {
                    break;
                }
            }
        }
        $children = array_values(array_map(function ($node) {
            return $node->id;
        }, $node->getChildren()));
        $ids = array_filter(array_unique(array_merge([$id], $children, $ancestors)));
        $data = $this->db->all(
            "SELECT * FROM tree_data WHERE published = 1 AND id IN (??) AND lang = ?",
            [ $ids, $lang ],
            'id'
        );
        if (!isset($data[$id])) {
            throw new PageNotFoundException();
        }
        $data = $this->normalizeData($data);
        $ancestors = array_map(function ($a) use ($data, $lang) {
            if (!isset($data[$a])) {
                throw new PageNotFoundException();
            }
            return $this->pages[$lang . '/' . $a] ?? ($this->pages[$lang . '/' . $a] = new Page($this, $data[$a]));
        }, $ancestors);
        $children = array_filter(array_map(function ($c) use ($data, $lang) {
            if (!isset($data[$c])) {
                return null;
            }
            return $this->pages[$lang . '/' . $c] ?? ($this->pages[$lang . '/' . $c] = new Page($this, $data[$c]));
        }, $children));
        return $this->pages[$lang . '/' . $id] = new Page($this, $data[$id], $ancestors, $children);
    }
    public function getHomepage(int $lang = 1): Page
    {
        return $this->getPage(HOMEPAGE, $lang);
    }
    public function getPageByUrl(string $url): Page
    {
        $url = trim(explode('?', $url)[0], '/');
        if (!strlen($url)) {
            return $this->getHomepage();
        }
        if (in_array($url, array_keys($this->lang))) {
            return $this->getHomepage($this->langCodeToID($url));
        }
        $sql = [ 'd.url = ?' ];
        $par = [ $url ];
        $seg = explode('/', $url);
        for ($i = count($seg) - 1; $i >= 0; $i--) {
            $par[] = implode('/', $seg) . '/*';
            $sql[] = 'd.url = ?';
            unset($seg[$i]);
        }
        $sql = implode(' OR ', $sql);
        $par[] = $this->tree->getNode(HOMEPAGE)->lft;
        $par[] = $this->tree->getNode(HOMEPAGE)->rgt;
        $dat = $this->db->one(
            "SELECT d.id, d.lang
             FROM tree_data d, tree_struct s
             WHERE d.id = s.id AND d.published = 1 AND d.hidden = 0 AND (${sql}) AND s.lft >= ? AND s.rgt <= ?
             ORDER BY LENGTH(url) DESC",
            $par
        );
        if (!$dat) {
            throw new PageNotFoundException();
        }
        return $this->getPage($dat['id'], $dat['lang']);
    }
    public function getPageVersion(int $id, int $lang, int $version): Page
    {
        $node = $this->tree->getNode($id);
        if (!$node) {
            throw new PageNotFoundException();
        }
        $ancestors = array_values(
            array_map(
                function ($node) {
                    return $node->id;
                },
                array_filter($node->getAncestors(), function ($node) {
                    return $node->getParent() !== null;
                })
            )
        );
        $children = array_values(array_map(function ($node) {
            return $node->id;
        }, $node->getChildren()));
        $ids = array_filter(array_unique(array_merge([$id], $children, $ancestors)));
        $data = $this->db->all(
            "SELECT * FROM tree_data WHERE published = 1 AND id IN (??) AND lang = ?",
            [ $ids, $lang ],
            'id'
        );
        $data[$id] = $this->db->one(
            "SELECT * FROM tree_data WHERE id = ? AND lang = ? AND version = ?",
            [ $id, $lang, $version ],
            'id'
        );
        if (!isset($data[$id])) {
            throw new PageNotFoundException();
        }
        $data = $this->normalizeData($data);
        $ancestors = array_map(function ($a) use ($data) {
            if (!isset($data[$a])) {
                throw new PageNotFoundException();
            }
            return new Page($this, $data[$a], null, null);
        }, $ancestors);
        $children = array_map(function ($c) use ($data) {
            if (!isset($data[$c])) {
                throw new PageNotFoundException();
            }
            return new Page($this, $data[$c], null, null);
        }, $children);
        return new Page($this, $data[$id], $ancestors, $children);
    }
    public function getPageFromToken(string $token): Page
    {
        try {
            $token = \vakata\jwt\JWT::fromString($token);
            if (!$token->verify(SIGNATUREKEY) || !$token->isValid()) {
                throw new \Exception();
            }
            if (!$token->hasClaim('i') || !$token->hasClaim('l') || !$token->hasClaim('v')) {
                throw new \Exception();
            }
            return $this->getPageVersion($token->getClaim('i'), $token->getClaim('l'), $token->getClaim('v'));
        } catch (\Exception $e) {
            throw new PageNotFoundException();
        }
    }
    public function getTopMenu(int $lang): array
    {
        $site = $this->site($lang);
        $temp = $this->db->all(
            'SELECT
                dd.id, dd.lang, dd.version, dd.from_version, dd.created, dd.usr, dd.title, dd.hidden, dd.url,
                dd.redirect, dd.settings, dd.content, dd.permissions, dd.template, dd.menu_top, dd.menu_lft,
                dd.menu_rgt, dd.published, ss.pid
            FROM
                tree_struct s
            JOIN
                tree_data d ON s.id = d.id AND
                d.hidden = 0 AND
                d.published = 1 AND
                d.lang = ? AND
                d.menu_top = 1
            JOIN
                tree_struct ss ON ss.lft >= s.lft AND
                ss.rgt <= s.rgt AND
                ss.lvl <= s.lvl + 2
            JOIN
                tree_data dd ON ss.id = dd.id AND
                dd.hidden = 0 AND
                dd.published = 1 AND
                dd.lang = ?
            WHERE
                s.pid = ? 
            GROUP BY
                dd.id, dd.lang, dd.version, dd.from_version, dd.created, dd.usr, dd.title, dd.hidden, dd.url,
                dd.redirect, dd.settings, dd.content, dd.permissions, dd.template, dd.menu_top, dd.menu_lft,
                dd.menu_rgt, dd.published, ss.pid
            ORDER BY ss.lft',
            [$lang, $lang, $site['tree']],
            'id'
        );
        $data = [];
        foreach ($temp as $node) {
            if ((int) $node['menu_top']) {
                $node['url'] = $this->getUrl(
                    (int) $node['id'],
                    (int) $node['lang'],
                    json_decode($node['settings'], true) ?? []
                );
                $data[$node['id']] = $node;
            }
        }
        foreach ($data as $key => $value) {
            $data[$key]['children'] = [];
            foreach ($temp as $node) {
                $settings = json_decode($node['settings'], true) ?? [];
                if ((int) $node['pid'] === (int) $key && isset($settings['menu']) && (int) $settings['menu']) {
                    $node['url'] = $this->getUrl((int) $node['id'], (int) $node['lang'], $settings);
                    $data[$key]['children'][$node['id']] = $node;
                }
            }
            foreach ($data[$key]['children'] as $child) {
                $data[$key]['children'][$child['id']]['children'] = [];
                foreach ($temp as $node) {
                    $settings = json_decode($node['settings'], true) ?? [];
                    if ((int) $node['pid'] === (int) $child['id'] &&
                        isset($settings['menu']) &&
                        (int) $settings['menu']
                    ) {
                        $node['url'] = $this->getUrl((int) $node['id'], (int) $node['lang'], $settings);
                        $data[$key]['children'][$child['id']]['children'][$node['id']] = $node;
                    }
                }
            }
        }

        return $data;
    }
    protected function getUrl(int $id, int $lang, array $settings)
    {
        if (isset($settings['redirect']) && strlen($settings['redirect'])) {
            return $settings['redirect'];
        }
        if (isset($settings['url']) && strlen($settings['url'])) {
            return trim($settings['url'], '/*');
        }
        return $this->langIDToCode($lang) . '/' . $id;
    }
    public function site(int $lang)
    {
        $data = $this->db->one('SELECT * FROM sites WHERE site = ?', [ SITE ]);
        $data['name'] = (int) $lang === 1 ? $data['name_bg'] : $data['name_en'];

        return $data;
    }
    public function footer(int $lang)
    {
        return json_decode(
            $this->db->one('SELECT data FROM footer WHERE lang = ? AND site = ?', [ $lang, SITE ]),
            true
        ) ?? [];
    }
}
