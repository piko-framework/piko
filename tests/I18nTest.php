<?php
use PHPUnit\Framework\TestCase;

use piko\Piko;
use piko\Application;

class I18nTest extends TestCase
{

    public function TestI18nApplication()
    {
        $config = [
            'basePath' => __DIR__,
            'language' => 'fr',
            'components' => [
                'i18n' => [
                    'class' => 'piko\I18n',
                    'translations' => [
                        'test' => '@app/messages'
                    ]
                ],
            ],
        ];

        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['SCRIPT_FILENAME'] = '';

        new Application($config);

        $message = Piko::t('test', 'Translation test');

        $this->assertEquals('Test de traduction', $message);
    }

    public function testAddTranslation()
    {
        session_start();
        $config = [
            'basePath' => __DIR__,
            'language' => 'fr',
            'components' => [
                'i18n' => [
                    'class' => 'piko\I18n',
                ]
            ]
        ];

        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['SCRIPT_FILENAME'] = '';

        new Application($config);

        $i18n = Piko::get('i18n');
        $i18n->addTranslation('test', '@app/messages');

        $message = Piko::t('test', 'Translation test');

        $this->assertEquals('Test de traduction', $message);
    }
}
