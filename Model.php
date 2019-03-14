<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
namespace piko;

/**
 * Base model class.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Model extends Component
{
    /**
     * @var array Represents the model's data.
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
     */
    public function __set($attribute, $value)
    {
        $this->data[$attribute] = $value;
    }

    /**
     * Magick method to check if attribute is defined in model's data.
     *
     * @param string $attribute The attribute's name.
     */
    public function __isset($attribute)
    {
        return isset($this->data[$attribute]);
    }

    /** Magick method to unset attribute in model's data.
     *
     * @param string $attribute The attribute's name.
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
     */
    public function bind($data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Validate this model (Should be extended)
     *
     * @return boolean
     */
    public function validate()
    {
        return true;
    }
}
