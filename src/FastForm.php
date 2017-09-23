<?php

namespace WebComplete\form;

final class FastForm extends AbstractForm
{

    /**
     * FastForm constructor.
     *
     * @param array $rules
     * @param array $filters
     * @param $validatorsObject
     * @param $filtersObject
     */
    public function __construct($rules = [], $filters = [], $validatorsObject = null, $filtersObject = null)
    {
        parent::__construct(
            $rules,
            $filters,
            $validatorsObject ?? new Validators(),
            $filtersObject ?? new Filters()
        );
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function filters(): array
    {
        return [];
    }
}
