<?php
use PHPUnit\Framework\TestCase;

use tests\modules\test\models\ContactForm;


class ModelTest extends TestCase
{
    public function testBindAndValidate()
    {
        $form = new ContactForm();

        $data = [
            'name' => 'Toto',
            'email' => 'toto@gmail.com',
            'message' => 'Hello!',
        ];

        $form->bind($data);

        $this->assertEquals($data, $form->toArray());

        $this->assertTrue($form->isValid());

        $form->email = '';

        $this->assertFalse($form->isValid());

        $this->assertContains('Email is required', $form->getErrors());

    }

}
