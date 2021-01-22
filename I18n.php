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
 * Internationalization class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class I18n extends Component
{
    /**
     * A key / values pairs of domain / path
     *
     * @var array
     */
    public $translations = [];

    /**
     * Messages container by domain
     *
     * @var array
     */
    protected $messages = [];

    /**
     * {@inheritDoc}
     * @see \piko\Component::init()
     */
    protected function init()
    {
        foreach ($this->translations as $domain => $path) {
            $this->addTranslation($domain, $path);
        }
    }

    /**
     * Register a translation
     *
     * @param string $domain The translation domain, for instance 'app'.
     * @param string $path The path to the directory where to find translation files.
     * @return void
     */
    public function addTranslation($domain, $path)
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
    public function translate($domain, $text, $params = [])
    {
        $text = isset($this->messages[$domain][$text]) ? $this->messages[$domain][$text] : $text;

        foreach ($params as $k => $v) {
            $text = str_replace('{' . $k . '}', $v, $text);
        }

        return $text;
    }
}
