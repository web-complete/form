<?php

use WebComplete\form\AbstractForm;

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

    /**
     * @param $value
     * @param $params
     * @param AbstractForm $form
     * - field
     *
     * @return bool
     */
    public function repeatPassword($value, $params, AbstractForm $form)
    {
        return $value == $form->getValue($params['field']);
    }

}