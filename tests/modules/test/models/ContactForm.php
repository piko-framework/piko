<?php
namespace tests\modules\test\models;

/**
 * This is a class reprensenting a contact form.
 */
class ContactForm extends \piko\Model
{
    public $name = 'tata';
    public $email = '';
    public $message = '';

    protected function validate(): void
    {
        if ($this->name === '') {
            $this->errors['name'] = 'Name is required';
        }

        if ($this->email === '') {
            $this->errors['email'] = 'Email is required';
        }

        parent::validate();
    }
}