<?php

class Filters
{

    /**
     * @param $value
     * @param $params
     * - amount
     *
     * @return mixed
     */
    public function increase($value, $params)
    {
        if(isset($params['amount'])) {
            $value += (int)$params['amount'];
        }
        return $value;
    }

}