<?php

namespace WebComplete\form;

trait TraitErrors
{

    protected $errors = [];

    /**
     * @param string $field
     */
    public function resetErrors($field = null)
    {
        if ($field !== null && $field) {
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
     *
     * @return bool
     */
    public function hasErrors($field = null): bool
    {
        return \count($this->getErrors($field)) > 0;
    }

    /**
     * @param string|null $field
     *
     * @return array
     */
    public function getErrors($field = null): array
    {
        if ($field !== null && $field) {
            return $this->errors[$field] ?? [];
        }

        return $this->errors;
    }

    /**
     * @return array
     */
    public function getFirstErrors(): array
    {
        $result = [];
        foreach ($this->getErrors() as $field => $errors) {
            if ($errors) {
                $result[$field] = \reset($errors);
            }
        }

        return $result;
    }

    /**
     * @param $field
     *
     * @return string|null
     */
    public function getFirstError($field)
    {
        return isset($this->errors[$field]) && $this->errors[$field] ? \reset($this->errors[$field]) : null;
    }
}
