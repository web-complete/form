<?php

namespace WebComplete\form;

abstract class AbstractForm implements FormInterface
{
    use TraitErrors;

    const REQUIRED = 'required';

    protected $data = [];
    protected $defaultError = 'error';
    protected $filtersObject;
    protected $validatorsObject;
    private $rules;
    private $filters;

    /**
     * AbstractForm constructor.
     *
     * @param null|array $rules
     * @param null|array $filters
     * @param $filtersObject
     * @param $validatorsObject
     */
    public function __construct($rules = null, $filters = null, $validatorsObject = null, $filtersObject = null)
    {
        if (\is_object($filtersObject)) {
            $this->filtersObject = $filtersObject;
        }
        if (\is_object($validatorsObject)) {
            $this->validatorsObject = $validatorsObject;
        }

        $this->rules = \is_array($rules) ? \array_merge($this->rules(), $rules) : $this->rules();

        $this->filters = \is_array($filters) ? \array_merge($this->filters(), $filters) : $this->filters();
    }

    /**
     * @return bool
     * @throws \WebComplete\form\FormException
     */
    public function validate(): bool
    {
        /** @var array[] $definitions */
        $definitions = $this->normalize($this->rules);

        $this->resetErrors();
        $this->validateRequired($definitions);

        foreach ($definitions as $field => $fieldDefinitions) {
            $value = $this->getValue($field);
            foreach ($fieldDefinitions as $definition) {
                $defName = \array_shift($definition);
                $defParams = \array_merge([$value], [\array_shift($definition)], [$this]);
                $defMessage = \array_shift($definition) ?: $this->defaultError;

                if ($defName !== self::REQUIRED && !$this->isEmpty($value)
                    && !$this->call($defName, $defParams, $this->validatorsObject, true)) {
                    $this->addError($field, $defMessage);
                }
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     * @throws \WebComplete\form\FormException
     */
    public function setData(array $data)
    {
        $this->data = $this->filter($data);

        return $this;
    }

    /**
     * @param $field
     *
     * @return mixed|null
     */
    public function getValue($field)
    {
        return $this->getDataValue($this->data, $field);
    }

    /**
     * @param $field
     * @param $value
     * @param bool $filter
     *
     * @throws \WebComplete\form\FormException
     */
    public function setValue($field, $value, $filter = true)
    {
        if ($filter) {
            $data = $this->filter([$field => $value]);
            $value = $data[$field] ?? null;
        }
        $this->setDataValue($this->data, $field, $value);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \WebComplete\form\FormException
     */
    protected function filter(array $data): array
    {
        $filtersDefinitions = $this->normalize($this->filters);
        $rulesDefinitions = $this->normalize($this->rules);

        foreach ($data as $field => $value) {
            if (!$this->hasKey($rulesDefinitions, $field) && !$this->hasKey($filtersDefinitions, $field)) {
                unset($data[$field]);
                continue;
            }
        }

        if (isset($filtersDefinitions['*'])) {
            foreach ($filtersDefinitions as $field => $fieldDefinitions) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $filtersDefinitions[$field] = \array_merge($fieldDefinitions, $filtersDefinitions['*']);
            }
            foreach (\array_keys($rulesDefinitions) as $field) {
                if ($field !== '*' && !isset($filtersDefinitions[$field])) {
                    $filtersDefinitions[$field] = $filtersDefinitions['*'];
                }
            }
            unset($filtersDefinitions['*']);
        }

        foreach ($filtersDefinitions as $field => $fieldDefinitions) {
            $value = $this->getDataValue($data, $field);
            foreach ((array)$fieldDefinitions as $definition) {
                $defName = \array_shift($definition);
                $defParams = \array_merge([$value], [\array_shift($definition)], [$this]);
                $value = $this->call($defName, $defParams, $this->filtersObject, $value);
            }
            $this->setDataValue($data, $field, $value);
        }

        return $data;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    protected function isEmpty($value): bool
    {
        return $value === null || $value === '' || (\is_array($value) && !\count($value));
    }

    /**
     * @param $definitions
     */
    protected function validateRequired($definitions)
    {
        /** @var array[] $definitions */
        foreach ($definitions as $field => $fieldDefinitions) {
            foreach ($fieldDefinitions as $definition) {
                if ($definition[0] === self::REQUIRED && $this->isEmpty($this->getValue($field))) {
                    $this->addError($field, !empty($definition[2]) ? $definition[2] : $this->defaultError);
                }
            }
        }
    }

    /**
     * @param $definitions
     *
     * @return array
     */
    private function normalize($definitions): array
    {
        $normalized = [];
        /** @var array[] $definitions */
        foreach ($definitions as $definition) {
            $fields = \array_shift($definition);
            $defName = $definition ? \array_shift($definition) : null;
            $defParams = $definition ? \array_shift($definition) : [];
            $defMessage = $definition ? \array_shift($definition) : '';
            $fields = (array)$fields;
            foreach ($fields as $field) {
                if (!isset($normalized[$field])) {
                    $normalized[$field] = [];
                }
                $normalized[$field][] = [$defName, $defParams, $defMessage];
            }
        }

        return $normalized;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param $defName
     * @param $defParams
     * @param $object
     * @param $default
     *
     * @return mixed|null
     * @throws FormException
     */
    private function call($defName, $defParams, $object, $default)
    {
        $callable = $defName;
        if ($defName) {
            if (\is_string($defName)) {
                if (\method_exists($this, $defName)) {
                    $callable = [$this, $defName];
                } elseif ($object && \method_exists($object, $defName)) {
                    $callable = [$object, $defName];
                }
            }

            if (!\is_callable($callable)) {
                throw new FormException('Callable not found: ' . \json_encode($callable));
            }
        }

        return $callable ? \call_user_func_array($callable, $defParams) : $default;
    }

    /**
     * @param array $array
     * @param string $key
     *
     * @return bool
     */
    private function hasKey(array $array, string $key): bool
    {
        $keys = \array_keys($array);
        foreach ($keys as $arrayKey) {
            if ($arrayKey === $key || \strpos($arrayKey, $key . '.', 0) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $array
     * @param $path
     *
     * @return mixed|null
     */
    private function getDataValue($array, $path)
    {
        $array = (array)$array;
        if (\is_array($array) && (isset($array[$path]) || \array_key_exists($path, $array))) {
            return $array[$path];
        }

        if (($pos = \strrpos($path, '.')) !== false) {
            $array = $this->getDataValue($array, \substr($path, 0, $pos));
            $path = (string)\substr($path, $pos + 1);
        }

        if (\is_array($array)) {
            return (isset($array[$path]) || \array_key_exists($path, $array)) ? $array[$path] : null;
        }

        return null;
    }

    /**
     * @param array $array
     * @param $path
     * @param $value
     */
    private function setDataValue(array &$array, $path, $value)
    {
        if ($path === null) {
            $array = $value;
            return;
        }

        $keys = \is_array($path) ? $path : \explode('.', $path);

        while (\count($keys) > 1) {
            $key = \array_shift($keys);
            if (!isset($array[$key])) {
                $array[$key] = [];
            }
            if (!\is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
            $array = &$array[$key];
        }

        $array[\array_shift($keys)] = $value;
    }
}
