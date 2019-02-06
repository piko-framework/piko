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
     * Bind directly the model data.
     *
     * @param array $data An array of data (name-value pairs).
     */
    public function bind($data)
    {
        $this->data = $data;
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
