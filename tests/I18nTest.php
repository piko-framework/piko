<?php
use PHPUnit\Framework\TestCase;
use piko\Application;
use piko\Piko;

class I18nTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['SCRIPT_FILENAME'] = '';
    }

    protected function tearDown(): void
    {
        Piko::reset();
    }

    public function testWithConfig()
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

        new Application($config);

        $this->assertEquals('Test de traduction', Piko::t('test', 'Translation test'));
        $this->assertEquals('Bonjour Toto', Piko::t('test', 'Hello {name}', ['name' => 'Toto']));
    }

    public function testAddTranslation()
    {
        $config = [
            'basePath' => __DIR__,
            'language' => 'fr',
            'components' => [
                'i18n' => [
                    'class' => 'piko\I18n',
                ]
            ]
        ];

        new Application($config);

        $i18n = Piko::get('i18n');
        $i18n->addTranslation('test', '@app/messages');

        $this->assertEquals('Test de traduction', Piko::t('test', 'Translation test'));
        $this->assertEquals('Bonjour Toto', Piko::t('test', 'Hello {name}', ['name' => 'Toto']));
    }

    public function testUnregisteredTranslation()
    {
        $this->assertEquals('Translation test', Piko::t('test', 'Translation test'));
        $this->assertEquals('Hello Toto', Piko::t('test', 'Hello {name}', ['name' => 'Toto']));
    }
}
