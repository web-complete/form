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
        foreach ($this->getData() as $field => $value) {
            if (isset($definitions[$field])) {
                foreach ($definitions[$field] as $definition) {
                    $defName = \array_shift($definition);
                    $defParams = \array_merge([$value], [\array_shift($definition)], [$this]);
                    $defMessage = \array_shift($definition) ?: $this->defaultError;

                    if ($defName !== self::REQUIRED && !$this->isEmpty($value)
                        && !$this->call($defName, $defParams, $this->validatorsObject, true)) {
                        $this->addError($field, $defMessage);
                    }
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
        return $this->data[$field] ?? null;
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
        $this->data[$field] = $value;
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
            if (!isset($rulesDefinitions[$field]) && !isset($filtersDefinitions[$field])) {
                unset($data[$field]);
                continue;
            }

            $fieldDefinitions = $filtersDefinitions[$field] ?? [];
            if (isset($filtersDefinitions['*'])) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $fieldDefinitions = \array_merge($fieldDefinitions, $filtersDefinitions['*']);
            }

            foreach ($fieldDefinitions as $definition) {
                $defName = \array_shift($definition);
                $defParams = \array_merge([$value], [\array_shift($definition)], [$this]);
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
                    $this->addError($field, $definition[2] ?? $this->defaultError);
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
            if (!\is_array($defName)) {
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
}
