<?php

namespace WebComplete\form;

final class FastForm extends AbstractForm
{


    public function __construct($rules = [], $filters = [], $validatorsObject = null, $filtersObject = null)
    {
        if (!$validatorsObject) {
            $validatorsObject = new Validators();
        }
        if (!$filtersObject) {
            $filtersObject = new Filters();
        }
        parent::__construct($rules, $filters, $validatorsObject, $filtersObject);
    }

    /**
     * @return array
     */
    protected function rules()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function filters()
    {
        return [];
    }
}
