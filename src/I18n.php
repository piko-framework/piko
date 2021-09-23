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

/**
 * Internationalization class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class I18n extends Component
{
    /**
     * Messages container by domain
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Constructor
     *
     * $config argument should contains the key translations giving
     * a key / values pairs of domain / path.
     *
     * Example :
     *
     * ```php
     * [
     *   'translations' => [
     *     'domain' => '@app/messages'
     *   ]
       ]
       ```
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (isset($config['translations'])) {
            foreach ($config['translations'] as $domain => $path) {
                $this->addTranslation($domain, $path);
            }
        }

        parent::__construct($config);
    }

    /**
     * Register a translation
     *
     * @param string $domain The translation domain, for instance 'app'.
     * @param string $path The path to the directory where to find translation files.
     * @return void
     */
    public function addTranslation(string $domain, string $path): void
    {
        $this->trigger('addTranslation', [$domain, $path]);
        $this->messages[$domain] = require Piko::getAlias($path) . '/' . Piko::$app->language . '.php';
    }

    /**
     * Translate a text.
     *
     * @param string $domain The translation domain, for instance 'app'.
     * @param string $text The text to translate.
     * @param array $params Parameters substitution, eg. $this->translate('site', 'Hello {name}', ['name' => 'John']).
     *
     * @return string The translated text or the text itself if no translation was found.
     */
    public function translate(string $domain, ?string $text, array $params = []): string
    {
        $text = isset($this->messages[$domain][$text]) ? $this->messages[$domain][$text] : $text;

        foreach ($params as $k => $v) {
            $text = str_replace('{' . $k . '}', $v, $text);
        }

        return $text;
    }
}
