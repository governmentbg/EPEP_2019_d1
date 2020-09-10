<?php

namespace site\data;

class ScheduleService
{
    private const URL = 'https://portalextensions.justice.bg/api/public/gethearings';
    private const FIELDS = [ 'from', 'to', 'courtCode', 'hearingType', 'casenumber', 'caseyear', 'casetype' ];

    public function getHearingTypes()
    {
        return [ 'Открито' => 'Открито', 'Закрито' => 'Закрито' ];
    }
    public function getCaseTypes()
    {
        return [
            'Гражданско' => 'Гражданско',
            'Административно' => 'Административно',
            'Наказателно' => 'Наказателно',
            'Фирмено' => 'Фирмено',
            'Търговско' => 'Търговско'
        ];
    }
    protected function encode(array $data, $key = null)
    {
        $query = [];
        foreach ($data as $k => $v) {
            if (is_int($k)) {
                $k = urlencode((string)$k);
            }
            if ($key) {
                if (is_numeric($k)) {
                    $k = $key . '[]';
                } else {
                    $k = $key . '[' . urlencode($k) . ']';
                }
            }
            if (is_array($v)) {
                $query[] = $this->encode($v, $k);
            } else {
                $query[] = $k . '=' . urlencode($v);
            }
        }
        return implode('&', $query);
    }
    public function request(array $params)
    {
        if (isset($params['from']) && strlen($params['from']) && strtotime($params['from']) > 0) {
            $params['from'] = date('Y-m-d', strtotime($params['from']));
        }
        if (isset($params['to']) && strlen($params['to']) && strtotime($params['to']) > 0) {
            $params['to'] = date('Y-m-d', strtotime($params['to']));
        }
        $headers = [
            'Content-type' => 'application/json'
        ];
        $options = [
            "http" => [
                "method"        => "GET",
                "header"        => implode(
                    "\r\n",
                    array_map(
                        function ($header, $value) {
                            return $header . ": " . $value;
                        },
                        array_keys($headers),
                        $headers
                    )
                ),
                "timeout"       => 30,
                "ignore_errors" => true
            ]
        ];
        foreach ($params as $key => $value) {
            if (!strlen(trim($value)) || !in_array($key, static::FIELDS)) {
                unset($params[$key]);
            }
        }

        try {
            $response = fopen(
                trim(static::URL, '/') . "?" . $this->encode($params),
                "r",
                false,
                stream_context_create($options)
            );
            $body = stream_get_contents($response);

            $code = null;
            foreach (stream_get_meta_data($response)["wrapper_data"] ?? [] as $key => $header) {
                if ($key === 0 && strpos($header, "HTTP/") === 0) {
                    $code = explode(' ', $header)[1] ?? null;
                    break;
                }
            }
            fclose($response);
        } catch (\Throwable $e) {
            throw new \Exception('schedule.errors.tryagainlater');
        }

        $data = (int) $code === 200 ? json_decode($body, true) : [];

        return array_filter($data, function ($item) {
            return $item['HearingType'] == 'Открито';
        });
    }
}
