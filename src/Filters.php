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
    public function trim($value, array $params)
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
     * @param array $params
     * - allowableTags @see strip_tags()
     *
     * @return string
     */
    public function stripTags($value, array $params)
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
        return preg_replace('/<script(.*?)>(.*?)</script>/is', '', $value);
    }

}