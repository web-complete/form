<?php

class Validators
{

    /**
     * @param $value
     * @param $params
     * - min
     * - max
     * @return bool
     */
    public function number($value, $params)
    {
        if($value) {
            if($value < $params['min'] || $value > $params['max']) {
                return false;
            }
        }
        return true;
    }

}