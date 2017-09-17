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
    public function trim($value, array $params = [])
    {
        $charlist = isset($params['charlist']) ? $params['charlist'] : ' ';
        $left = isset($params['left']) ? (bool)$params['left'] : true;
        $right = isset($params['right']) ? (bool)$params['right'] : true;
        if($left && $right) {
            return trim($value, $charlist);
        }
        if($left) {
            return ltrim($value, $charlist);
        }
        if($right) {
            return rtrim($value, $charlist);
        }
        return $value;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function escape($value)
    {
        return htmlspecialchars($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function capitalize($value)
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
    public function lowercase($value)
    {
        return mb_strtolower($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function uppercase($value)
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
    public function replace($value, $params)
    {
        $pattern = isset($params['pattern']) ? $params['pattern'] : '';
        $to = isset($params['to']) ? $params['to'] : '';
        try {
            return preg_replace($pattern, $to, $value);
        }
        catch (\Exception $e) {}
        return str_replace($pattern, $to, $value);
    }

    /**
     * @param $value
     * @param array $params
     * - allowableTags @see strip_tags()
     *
     * @return string
     */
    public function stripTags($value, array $params = [])
    {
        $allowableTags = isset($params['allowableTags']) ? $params['allowableTags'] : null;
        return strip_tags($value, $allowableTags);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function stripJs($value)
    {
        return preg_replace('/<script(.*?)>(.*?)<\/script>/', '', $value);
    }

}