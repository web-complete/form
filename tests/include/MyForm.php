<?php

use WebComplete\form\AbstractForm;


class MyForm extends AbstractForm
{

    /**
     * @return array
     */
    public function rules()
    {
        return [

        ];
    }

    /**
     * @return array
     */
    public function filters()
    {
        return [

        ];
    }

    /**
     * @param $value
     * @param $params
     * - amount
     *
     * @return mixed
     */
    public function filterDecrease($value, $params)
    {
        if(isset($params['amount'])) {
            $value -= (int)$params['amount'];
        }
        return $value;
    }

    public function validateString($value, $params)
    {
        if($value) {
            return strlen($value) >= $params['minLength'];
        }
        return true;
    }

}