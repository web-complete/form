<?php

namespace WebComplete\form;

class Validators
{

    /**
     * @param $value
     *
     * @return bool
     */
    public function required($value)
    {
        return !($value === null || $value === '');
    }

    /**
     * @param $value
     * @param array $params
     * - not : bool - not equals, default = false
     *
     * @return bool
     */
    public function equals($value, array $params)
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
     * @param array $params
     *
     * @return bool
     */
    public function email($value, array $params)
    {
        return preg_match('/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $value);
    }

    /**
     * @param $value
     * @param array $params
     *
     * @return bool
     */
    public function number($value, array $params)
    {
        // todo
    }

    /**
     * @param $value
     * @param array $params
     *
     * @return bool
     */
    public function string($value, array $params)
    {
        // todo
    }

    /**
     * @param $value
     * @param array $params
     *
     * @return bool
     */
    public function regex($value, array $params)
    {
        // todo
    }

}