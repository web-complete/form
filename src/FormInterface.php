<?php

namespace WebComplete\form;

interface FormInterface
{
    /**
     * @return array [[field, validator, params, message], ...]
     *
     * validator - is a string equals to method of ValidatorsObject, method of the form or callable.
     * Validator should be declared as ($value, $params) : bool
     * @see \WebComplete\form\Validators
     *
     * required - is an internal validator, checks the field is not empty
     * * (asterisk) - rule for all fields (declared in rules or filter)
     *
     * example
     * ```
     * [
     *      [['name', 'email'], 'required', [], 'Field is required'],
     *      ['name', 'string', ['min' => 2, 'max' => 50], 'Incorrect name'],
     *      ['email', 'email', [], 'Incorrect email'],
     *      [['description', 'label']], // safe fields
     *      ['price', 'methodValidator', [], 'Incorrect'],
     *      ['some', [SomeValidator::class, 'method'], ['customParam' => 100], 'Incorrect'],
     *      [['*'], 'regex', ['pattern' => '/^[a-z]$/'], 'Field is required'],
     * ]
     * ```
     */
    public function rules(): array;

    /**
     * @return array [[field, filter, params], ...]
     *
     * filter - is a string equals to method of FiltersObject, method of the form or callable
     * Filter should be declared as ($value, $params) : mixed, and return filtered value
     * @see \WebComplete\form\Filters
     *
     * * (asterisk) - rule for all fields (declared in rules or filter)
     *
     * example
     * ```
     * [
     *      [['first_name', 'last_name'], 'capitalize'],
     *      ['description', 'stripTags'],
     *      ['content', 'stripJs'],
     *      ['email', 'replace', ['pattern' => 'email.com', 'to' => 'gmail.com']],
     *      ['*', 'trim'],
     * ]
     * ```
     *
     */
    public function filters(): array;

    /**
     * @return bool
     * @throws \WebComplete\form\FormException
     */
    public function validate(): bool;

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @param array $data
     *
     * @return $this
     * @throws \WebComplete\form\FormException
     */
    public function setData(array $data);

    /**
     * @param $field
     *
     * @return mixed|null
     */
    public function getValue($field);

    /**
     * @param $field
     * @param $value
     * @param bool $filter
     *
     * @throws \WebComplete\form\FormException
     */
    public function setValue($field, $value, $filter = true);

    /**
     * @param string|null $field
     *
     * @return bool
     */
    public function hasErrors($field = null): bool;

    /**
     * @param string|null $field
     *
     * @return array
     */
    public function getErrors($field = null): array;

    /**
     * @param $field
     *
     * @return string|null
     */
    public function getFirstError($field);
}
