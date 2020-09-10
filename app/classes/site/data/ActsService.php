<?php

namespace site\data;

class ActsService
{
    private const URL = 'https://portalextensions.justice.bg/api/public/getacts';
    private const FIELDS = [ 'from', 'to', 'courtCode', 'actkindcode', 'casenumber', 'caseyear', 'casetype' ];

    public function getActKindCodes()
    {
        return [
            '5001' => 'Решение',
            '5002' => 'Определение',
            '5003' => 'Присъда',
            '5004' => 'Споразумение',
            '5005' => 'Разпореждане',
            '5006' => 'Протокол',
            '5007' => 'Заповед',
            '5008' => 'Становище'
        ];
    }
    public function getCaseTypes()
    {
        return [
            'Гражданско' => 'Гражданско',
            'Наказателно' => 'Наказателно',
            'Търговско' => 'Търговско',
            'Административно' => 'Административно',
            'Фирмено' => 'Фирмено'
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
        if (isset($params['from']) && strlen($params['from'])) {
            if (strtotime($params['from'] . ' 00:0:00') > strtotime(date('d.m.Y 23:59:00'))) {
                throw new \Exception('schedule.errors.from');
            }
            $params['from'] = date('Y-m-d', strtotime($params['from']));
        } else {
            unset($params['from']);
        }
        if (isset($params['to']) && strlen($params['to']) && strtotime($params['to']) > 0) {
            $params['to'] = date('Y-m-d', strtotime($params['to']));
        } else {
            unset($params['to']);
        }
        if (isset($params['from']) && !isset($params['to'])) {
            $params['to'] = date('Y-m-d');
        }
        $actkindcodes = $this->getActKindCodes();
        if (isset($params['actkindcode']) && !isset($actkindcodes[$params['actkindcode']])) {
            unset($params['actkindcode']);
        }
        $caseTypes = $this->getCaseTypes();
        if (isset($params['casetype']) && !isset($caseTypes[$params['casetype']])) {
            unset($params['casetype']);
        }
        if (isset($params['caseyear']) && !(int) $params['caseyear']) {
            unset($params['caseyear']);
        }

        foreach ($params as $key => $value) {
            if (!strlen(trim($value)) || !in_array($key, static::FIELDS)) {
                unset($params[$key]);
            }
        }
        if (isset($params['casenumber']) && !isset($params['caseyear'])) {
            throw new \Exception('acts.errors.year.required');
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
            throw new \Exception('acts.errors.tryagainlater');
        }

        return (int) $code === 200 ? json_decode($body, true) : [];
    }
}
