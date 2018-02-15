<?php

namespace WebComplete\form;

class Filters
{

    /**
     * @param $value
     * @param array $params
     * - charlist : string, default = ' '
     * - left : bool - left trim, default = true
     * - right : bool - right trim, default = true
     *
     * @return string
     */
    public function trim($value, array $params = []): string
    {
        $charlist = $params['charlist'] ?? ' ';
        $left = $params['left'] ?? true;
        $right = $params['right'] ?? true;
        if ($left && $right) {
            return trim($value, $charlist);
        }
        if ($left) {
            return ltrim($value, $charlist);
        }
        if ($right) {
            return rtrim($value, $charlist);
        }
        return $value;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function escape($value): string
    {
        return htmlspecialchars($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function capitalize($value): string
    {
        $value = $this->lowercase($value);
        return mb_strtoupper(mb_substr($value, 0, 1, 'UTF-8'), 'UTF-8') .
            mb_substr($value, 1, mb_strlen($value, 'UTF-8'), 'UTF-8');
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function lowercase($value): string
    {
        return mb_strtolower($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function uppercase($value): string
    {
        return mb_strtoupper($value);
    }

    /**
     * @param $value
     * @param array $params
     * - pattern : string or regex
     * - to : string
     *
     * @return string
     */
    public function replace($value, $params): string
    {
        $pattern = $params['pattern'] ?? '';
        $to = $params['to'] ?? '';
        try {
            return preg_replace($pattern, $to, $value);
        } catch (\Exception $e) {
            // ignore
        }
        return str_replace($pattern, $to, $value);
    }

    /**
     * @param $value
     * @param array $params
     * - allowableTags @see strip_tags()
     *
     * @return string
     */
    public function stripTags($value, array $params = []): string
    {
        $allowableTags = $params['allowableTags'] ?? null;
        return strip_tags($value, $allowableTags);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function stripJs($value): string
    {
        return preg_replace('/<script(.*?)>(.*?)<\/script>/', '', $value);
    }
}
