<?php
namespace Piko\Tests\modules\test\models;

/**
 * This is a class reprensenting a contact form.
 */
class ContactForm
{
    use \Piko\ModelTrait;

    public $name = 'tata';
    public $email = '';
    public $message = '';

    protected function validate(): void
    {
        if ($this->name === '') {
            $this->setError('name', 'Name is required');
        }

        if ($this->email === '') {
            $this->setError('email', 'Email is required');
        }
    }
}