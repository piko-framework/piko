<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2021 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace piko;

use ReflectionClass;
use ReflectionProperty;

/**
 * Base model class.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
abstract class Model extends Component
{
    /**
     * Errors hash container
     *
     * @var array<string>
     */
    protected $errors = [];

    /**
     * Get the public properties reprenting the data model
     *
     * @return array<mixed>
     */
    protected function getAttributes()
    {
        $class = get_called_class();
        $reflection = new ReflectionClass($class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $attributes = [];

        foreach ($properties as $property) {
            /* @var $property ReflectionProperty */
            if ($property->class === $class) {
                $attributes[$property->name] = $property->getValue($this);
            }
        }

        return $attributes;
    }

    /**
     * Bind directly the model data.
     *
     * @param array<mixed> $data An array of data (name-value pairs).
     * @return void
     */
    public function bind(array $data): void
    {
        $attributes = $this->getAttributes();

        foreach ($data as $key => $value) {
            if (isset($attributes[$key])) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get the model data as an associative array.
     *
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->getAttributes();
    }

    /**
     * Return the errors hash container
     *
     * @return array<string>
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Validate this model (Should be extended)
     *
     * @return void
     */
    protected function validate(): void
    {
    }

    /**
     * Check if the model is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $this->validate();

        return empty($this->errors);
    }
}
