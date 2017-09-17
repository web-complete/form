<?php

namespace WebComplete\form;

class Validators
{

    /**
     * @param $value
     * @param array $params
     * - not : bool - not equals, default = false
     *
     * @return bool
     */
    public function equals($value, array $params = [])
    {
        $compareValue = isset($params['value']) ? $params['value'] : $value;
        $not = isset($params['not']) ? (bool)$params['not'] : false;

        return $not
            ? $value != $compareValue
            : $value == $compareValue;
    }

    /**
     * @param $value
     * @param array $params
     * - field : string - field of a form to compare
     * - not : bool - not equals, default = false
     *
     * @param AbstractForm $form
     *
     * @return bool
     */
    public function compare($value, array $params, AbstractForm $form)
    {
        $result = true;
        $field = isset($params['field']) ? $params['field'] : false;
        $not = isset($params['not']) ? (bool)$params['not'] : false;
        if($field) {
            $result = $not
                ? $value != $form->getValue($field)
                : $value == $form->getValue($field);
        }

        return $result;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function email($value)
    {
        return preg_match('/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $value) > 0;
    }

    /**
     * @param $value
     * @param array $params
     * - min : float - min value, default = null
     * - max : float - max value, default = null
     *
     * @return bool
     */
    public function number($value, array $params = [])
    {
        if(!is_numeric($value)) {
            return false;
        }
        if($min = isset($params['min']) ? $params['min'] : null) {
            if($value < $min) {
                return false;
            }
        }
        if($max = isset($params['max']) ? $params['max'] : null) {
            if($value > $max) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $value
     * @param array $params
     * - min : int - min length, default = null
     * - max : int - max length, default = null
     *
     * @return bool
     */
    public function string($value, array $params = [])
    {
        if($min = isset($params['min']) ? $params['min'] : null) {
            if(mb_strlen($value) < $min) {
                return false;
            }
        }
        if($max = isset($params['max']) ? $params['max'] : null) {
            if(mb_strlen($value) > $max) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $value
     * @param array $params
     * - pattern : regex
     *
     * @return bool
     */
    public function regex($value, array $params = [])
    {
        $pattern = isset($params['pattern']) ? $params['pattern'] : '//';
        return preg_match($pattern, $value) > 0;
    }

}