<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
declare(strict_types=1);

namespace piko;

/**
 * Base model class.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
abstract class Model extends Component
{
    /**
     * Represents the model's data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Magick method to access model's data as class attribute.
     *
     * @param string $attribute The attribute's name.
     * @return mixed The attribute's value.
     */
    public function __get($attribute)
    {
        return isset($this->data[$attribute]) ? $this->data[$attribute] : null;
    }

    /**
     * Magick method to set model's data as class attribute.
     *
     * @param string $attribute The attribute's name.
     * @param mixed $value The attribute's value.
     * @return void
     */
    public function __set($attribute, $value)
    {
        $this->data[$attribute] = $value;
    }

    /**
     * Magick method to check if attribute is defined in model's data.
     *
     * @param string $attribute The attribute's name.
     * @return boolean
     */
    public function __isset($attribute)
    {
        return isset($this->data[$attribute]);
    }

    /**
     * Magick method to unset attribute in model's data.
     *
     * @param string $attribute The attribute's name.
     * @return void
     */
    public function __unset($attribute)
    {
        if (isset($this->data[$attribute])) {
            unset($this->data[$attribute]);
        }
    }

    /**
     * Bind directly the model data.
     *
     * @param array $data An array of data (name-value pairs).
     * @return void
     */
    public function bind(array $data): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * Get the model data as an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Validate this model (Should be extended)
     *
     * @return boolean
     */
    public function validate(): bool
    {
        return true;
    }
}
