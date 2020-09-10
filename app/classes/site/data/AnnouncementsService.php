<?php

namespace site\data;

class AnnouncementsService
{
    public function listing(string $url = null, int $page = 1, int $limit = 12) : array
    {
        if (!$url) {
            return [];
        }

        $url = parse_url($url);
        $base = ($url['scheme'] ?? 'https') . '://' . $url['host'];
        $params = [];
        parse_str($url['query'], $params);
        $params['p'] = $page;
        $params['perpage'] = $limit;

        $temp = json_decode(@file_get_contents($base . '/' . trim(($url['path'] ?? ''), '/') . '?' . http_build_query($params)), true) ?? [];

        $key = null;
        $ids = [ 'properties' => 'property', 'vehicles' => 'vehicle', 'assets' => 'asset' ];
        $id = null;
        foreach ([ 'properties', 'vehicles', 'assets' ] as $type) {
            if (isset($temp[$type]) && is_array($temp[$type])) {
                $key = $type;
                $id = $ids[$key];
                break;
            }
        }

        if (!$key) {
            return [];
        }
        $data = [
            'currentPage'   => $temp['page'] ?? 1,
            'count'         => $temp['count'] ?? 0,
            'perpage'       => $temp['limit'] ?? 12,
            'type'          => $key,
            'data'          => []
        ];

        foreach ($temp[$key] as $row) {
            if (isset($row['files']) && is_array($row['files'])) {
                foreach ($row['files'] as $k => $value) {
                    if (isset($value['url'])) {
                        $row['files'][$k]['url'] = $base . '/' . $value['url'];
                    }
                }
            }
            $row['url'] = $base . '/' . ($key === 'assets' ? 'asset' : $key) . '/' . $row[$id] ?? '';
            $data['data'][] = $row;
        }

        return $data;
    }
}
