<?php

namespace WebComplete\form;


final class FastForm extends AbstractForm
{


    public function __construct($rules = [], $filters = [], $validatorsObject = null, $filtersObject = null)
    {
        parent::__construct($rules, $filters, $validatorsObject, $filtersObject);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return array
     */
    public function filters()
    {
        return [];
    }

}