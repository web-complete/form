<?php

namespace WebComplete\form;

abstract class AbstractForm
{

    const REQUIRED = 'required';

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
    private $rules;
    private $filters;

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
    ) {
    
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
    abstract protected function rules();

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
    abstract protected function filters();

    /**
     * @return bool
     */
    public function validate()
    {
        $definitions = $this->normalize($this->rules);

        $this->resetErrors();
        foreach ($definitions as $field => $fieldDefinitions) {
            foreach ($fieldDefinitions as $definition) {
                if ($definition[0] === self::REQUIRED && $this->isEmpty($this->getValue($field))) {
                    $this->addError($field, isset($definition[3]) ? $definition[3] : $this->defaultError);
                }
            }
        }
        foreach ($this->getData() as $field => $value) {
            if (isset($definitions[$field])) {
                foreach ($definitions[$field] as $definition) {
                    $defName = array_shift($definition);
                    $defParams = array_merge([$value], [array_shift($definition)], [$this]);
                    $defMessage = array_shift($definition) ?: $this->defaultError;

                    if ($defName !== self::REQUIRED && !$this->isEmpty($value)) {
                        if (!$this->call($defName, $defParams, $this->validatorsObject, true)) {
                            $this->addError($field, $defMessage);
                        }
                    }
                }
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
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
     * @param $field
     * @return mixed|null
     */
    public function getValue($field)
    {
        return isset($this->data[$field])
            ? $this->data[$field]
            : null;
    }

    /**
     * @param $field
     * @param $value
     * @param bool $filter
     */
    public function setValue($field, $value, $filter = true)
    {
        if ($filter) {
            $data = $this->filter([$field => $value]);
            $value = isset($data[$field])
                ? $data[$field]
                : null;
        }
        $this->data[$field] = $value;
    }

    /**
     * @param string $field
     */
    public function resetErrors($field = null)
    {
        if ($field) {
            unset($this->errors[$field]);
        } else {
            $this->errors = [];
        }
    }

    /**
     * @param $field
     * @param $error
     */
    public function addError($field, $error)
    {
        if (!isset($this->errors[$field])) {
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
        if ($field) {
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
            if ($errors) {
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
     * @param array $data
     * @return array
     */
    protected function filter(array $data)
    {
        $filtersDefinitions = $this->normalize($this->filters);
        $rulesDefinitions = $this->normalize($this->rules);

        foreach ($data as $field => $value) {
            if (!isset($rulesDefinitions[$field]) && !isset($filtersDefinitions[$field])) {
                unset($data[$field]);
                continue;
            }

            $fieldDefinitions = isset($filtersDefinitions[$field]) ? $filtersDefinitions[$field] : [];
            if (isset($filtersDefinitions['*'])) {
                $fieldDefinitions = array_merge($fieldDefinitions, $filtersDefinitions['*']);
            }

            foreach ($fieldDefinitions as $definition) {
                $defName = array_shift($definition);
                $defParams = array_merge([$value], [array_shift($definition)], [$this]);
                $data[$field] = $this->call($defName, $defParams, $this->filtersObject, $value);
            }
        }

        return $data;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    protected function isEmpty($value)
    {
        return $value === null || $value == '' || (is_array($value) && !count($value));
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
            if (!is_array($fields)) {
                $fields = [$fields];
            }
            foreach ($fields as $field) {
                if (!isset($normalized[$field])) {
                    $normalized[$field] = [];
                }
                $normalized[$field][] = [$defName, $defParams, $defMessage];
            }
        }

        return $normalized;
    }

    /**
     * @param $defName
     * @param $defParams
     * @param null|object $object
     * @param $default
     *
     * @return mixed|null
     * @throws FormException
     */
    private function call($defName, $defParams, $object, $default)
    {
        $callable = $defName;
        if ($defName) {
            if (!is_array($defName)) {
                if (method_exists($this, $defName)) {
                    $callable = [$this, $defName];
                } elseif ($object && method_exists($object, $defName)) {
                    $callable = [$object, $defName];
                }
            }

            if (!is_callable($callable)) {
                throw new FormException('Callable not found: ' . json_encode($callable));
            }
        }

        return $callable
            ? call_user_func_array($callable, $defParams)
            : $default;
    }
}
