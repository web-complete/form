<?php

namespace WebComplete\form;


abstract class AbstractForm
{

    private $rules;
    private $filters;

    protected $data = [];
    protected $errors = [];
    protected $defaultError = 'error';

    /**
     * @var object
     */
    protected $filtersObject;

    /**
     * @var object
     */
    protected $validatorsObject;

    /**
     * @return array [[field, validator, params, message], ...]
     *
     * validator - is a string equals to method of ValidatorsObject, method of the form or callable.
     * Validator should be declared as ($value, $params) : bool
     *
     * example
     * ```
     * [
     *      ['name', 'string', ['min' => 2, 'max' => 50], 'Incorrect name'],
     *      ['email', 'email', [], 'Incorrect email'],
     *      [['name', 'email'], 'required', [], 'Field is required'],
     *      [['description', 'label'], 'safe'],
     *      ['price', 'methodValidator', [], 'Incorrect'],
     *      ['some', [SomeValidator::class, 'method'], ['customParam' => 100], 'Incorrect'],
     *      [['*'], 'regexp', ['match' => '/^[a-z]$/'], 'Field is required'],
     * ]
     * ```
     */
    abstract public function rules();

    /**
     * @return array [[field, filter, params], ...]
     *
     * filter - is a string equals to method of FiltersObject, method of the form or callable
     * Filter should be declared as ($value, $params) : mixed, and return filtered value
     *
     * example
     * ```
     * [
     *      ['*', 'trim'],
     *      ['*', 'purify', ['js' => true]],
     *      [['first_name', 'last_name'], 'capitalize'],
     *      ['email', 'replace', ['from' => 'email.com', 'to' => 'gmail.com']],
     *      ['content', 'stripTags'],
     * ]
     * ```
     *
     */
    abstract public function filters();

    /**
     * AbstractForm constructor.
     * @param null|array $rules
     * @param null|array $filters
     * @param null|object $filtersObject
     * @param null|object $validatorsObject
     */
    public function __construct(
        $rules = null,
        $filters = null,
        $validatorsObject = null,
        $filtersObject = null
    )
    {
        $this->filtersObject = $filtersObject;
        $this->validatorsObject = $validatorsObject;

        $this->rules = is_array($rules)
            ? array_merge($this->rules(), $rules)
            : $this->rules();

        $this->filters = is_array($filters)
            ? array_merge($this->filters(), $filters)
            : $this->filters();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $this->filter($data);
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $definitions = $this->normalize($this->rules);

        $this->resetErrors();
        foreach ($this->getData() as $field => $value) {
            if(isset($definitions[$field])) {
                foreach ($definitions[$field] as $definition) {
                    $defName = array_shift($definition);
                    $defParams = array_merge([$value], [array_shift($definition)]);
                    $defMessage = array_shift($definition) ?: $this->defaultError;

                    if($defName) {
                        if(is_callable($defName)) {
                            $callable = $defName;
                        }
                        else if($this->validatorsObject && method_exists($this->validatorsObject, $defName)) {
                            $callable = [$this->validatorsObject, $defName];
                        }
                        else {
                            $callable = [$this, $defName];
                        }

                        if(!call_user_func_array($callable, $defParams)) {
                            $this->addError($field, $defMessage);
                        }
                    }
                }
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @param $field
     * @param $error
     */
    public function addError($field, $error)
    {
        if(!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $error;
    }

    /**
     * @param string|null $field
     * @return bool
     */
    public function hasErrors($field = null)
    {
        return count($this->getErrors($field)) > 0;
    }

    /**
     * @param string|null $field
     * @return array
     */
    public function getErrors($field = null)
    {
        if($field) {
            return isset($this->errors[$field])
                ? $this->errors[$field]
                : [];
        }
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getFirstErrors()
    {
        $result = [];
        foreach ($this->getErrors() as $field => $errors) {
            if($errors) {
                $result[$field] = reset($errors);
            }
        }
        return $result;
    }

    /**
     * @param $field
     * @return string|null
     */
    public function getFirstError($field)
    {
        return isset($this->errors[$field]) && $this->errors[$field]
            ? reset($this->errors[$field])
            : null;
    }

    /**
     * @param string $field
     */
    public function resetErrors($field = null)
    {
        if($field) {
            unset($this->errors[$field]);
        }
        else {
            $this->errors = [];
        }
    }

    /**
     * @param array $data
     * @return array
     */
    protected function filter(array $data)
    {
        $filtersDefinitions = $this->normalize($this->filters);
        $rulesDefinitions = $this->normalize($this->rules);

        foreach ($data as $field => $value) {
            if(!isset($rulesDefinitions[$field]) && !isset($filtersDefinitions[$field])) {
                unset($data[$field]);
                continue;
            }

            $fieldDefinitions = isset($filtersDefinitions[$field]) ? $filtersDefinitions[$field] : [];
            if(isset($filtersDefinitions['*'])) {
                $fieldDefinitions = array_merge($fieldDefinitions, $filtersDefinitions['*']);
            }

            foreach ($fieldDefinitions as $definition) {
                $defName = array_shift($definition);
                $defParams = array_merge([$value], [array_shift($definition)]);

                if($defName) {
                    if(is_callable($defName)) {
                        $callable = $defName;
                    }
                    else if($this->filtersObject && method_exists($this->filtersObject, $defName)) {
                        $callable = [$this->filtersObject, $defName];
                    }
                    else {
                        $callable = [$this, $defName];
                    }
                    $data[$field] = call_user_func_array($callable, $defParams);
                }
            }
        }

        return $data;
    }

    /**
     * @param $definitions
     * @return array
     */
    private function normalize($definitions)
    {
        $normalized = [];
        foreach ($definitions as $definition) {
            $fields = array_shift($definition);
            $defName = $definition ? array_shift($definition) : null;
            $defParams = $definition ? array_shift($definition) : [];
            $defMessage = $definition ? array_shift($definition) : '';
            if(!is_array($fields)) {
                $fields = [$fields];
            }
            foreach ($fields as $field) {
                if(!isset($normalized[$field])) {
                    $normalized[$field] = [];
                }
                $normalized[$field][] = [$defName, $defParams, $defMessage];
            }
        }

        return $normalized;
    }

}