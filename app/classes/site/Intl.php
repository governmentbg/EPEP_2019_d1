<?php

namespace site;

/**
 * Translator class
 */
class Intl
{
    protected $data = [];
    protected $priority = [];

    public function __construct(array $translations = [])
    {
        foreach ($translations as $code => $path) {
            $this->addLanguage($code, $path);
        }
    }
    public function addLanguage(string $code, string $path)
    {
        if (is_file($path . '.php')) {
            $lang = [];
            include $path . '.php';
            $this->data[$code] = \vakata\intl\Intl::fromArray($lang);
        } else {
            $this->data[$code] = \vakata\intl\Intl::fromFile($path);
        }
        $this->priority[] = $code;
    }
    public function setPriority(array $priority)
    {
        $this->priority = $priority;
    }

    public function getCode() : string
    {
        return array_values($this->priority)[0] ?? null;
    }

    /**
     * Get a translated string using its key in the translations array.
     * @param  array|string $key     the translation key, if an array all values will be checked until a match is found
     * @param  array        $replace any variables to replace with
     * @param  string|null  $default optional value to return if key is not found, `null` returns the key
     * @return string       the final translated string
     */
    public function get($key, array $replace = [], string $default = null) : string
    {
        foreach ($this->priority as $code) {
            if (isset($this->data[$code])) {
                $temp = $this->data[$code]->get($key, $replace, chr(1));
                if ($temp !== chr(1)) {
                    return $temp;
                }
            }
        }
        return $default !== null ?
            $default :
            (is_array($key) ? array_values($key)[0] : $key);
    }
    public function __invoke($key, array $replace = [], string $default = null) : string
    {
        return $this->get($key, $replace, $default);
    }
    public function number(float $number = 0.0, int $decimals = 0) : string
    {
        foreach ($this->priority as $code) {
            if (isset($this->data[$code])) {
                return $this->data[$code]->number($number, $decimals);
            }
        }
        return number_format($number, $decimals);
    }
    public function date(string $format = 'short', int $timestamp = null) : string
    {
        foreach ($this->priority as $code) {
            if (isset($this->data[$code])) {
                return $this->data[$code]->date($format, $timestamp);
            }
        }
        return date($format, $timestamp);
    }
}
