<?php

use WebComplete\form\AbstractForm;


class MyForm extends AbstractForm
{

    /**
     * @return array
     */
    protected function rules()
    {
        return [

        ];
    }

    /**
     * @return array
     */
    protected function filters()
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