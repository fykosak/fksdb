<?php

namespace FKSDB\Components\Controls\Choosers;


use Nette\Application\UI\Control;

use Nette\Http\Session;
use Nette\Templating\Template;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Jakub Šafin <xellos@fykos.cz>
 */
class LanguageChooser extends Control {

    const DEFAULT_FIRST = 'cs';

    /**
     * @var mixed
     */
    private $languages;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var mixed
     */
    private $language;
    /**
     * @var array
     */
    private $languageNames = ['cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina'];

    private $initialized = false;

    /**
     * @var mixed DEFAULT_*
     */
    private $defaultLanguage = self::DEFAULT_FIRST;

    /**
     *
     * @param Session $session
     */
    function __construct(Session $session) {
        parent::__construct();
        $this->session = $session;
    }

    /**
     * @param mixed $languages role enum|ALL_LANGUAGES|array of languages
     */
    public function setLanguages($languages) {
        $this->languages = $languages;
    }

    public function getLanguages() {
        return $this->languages;
    }

    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage($defaultLanguage) {
        $this->defaultLanguage = $defaultLanguage;
    }

    public function isLanguage($language) {
        return in_array($language, $this->languages);
    }

    /**
     * @param $params object
     * @return boolean
     * Redirect to correct address accorging to the resolved values.
     */
    public function syncRedirect(&$params) {
        $this->init($params);

        if ($this->language !== $params->lang) {
            $params->lang = $this->language;

            return true;
        }
        return false;
    }

    public function getLanguage() {
        return $this->language;
    }

    private function init($params) {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $this->setLanguages($this->getSupportedLanguages());

        if (count($this->getLanguages()) == 0) {
            return;
        }

        /* LANGUAGE */
        $this->language = $this->getDefaultLanguage();

        if ($params->lang !== null) {

            if (!$this->isLanguage($params->lang)) {
                $this->language = $this->getDefaultLanguage();
            } else {
                $this->language = $params->lang;
            }

        }
    }

    /**
     * @return array of existing languages
     */
    private function getSupportedLanguages() {
        /**
         * @var $presenter \AuthenticationPresenter
         */
        $presenter = $this->getPresenter();
        return $presenter->getTranslator()->getSupportedLanguages();

    }

    protected function createTemplate($class = NULL) {
        /**
         * @var $template Template
         */
        $template = parent::createTemplate($class);
        $template->setTranslator($this->getPresenter()->getTranslator());
        return $template;
    }

    public function render() {

        $this->template->languages = $this->getLanguages();
        $this->template->languageNames = $this->languageNames;
        $this->template->currentLanguage = $this->getLanguage() ? $this->getLanguage() : null;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR.'LanguageChooser.latte');
        $this->template->render();
    }

    public function handleChangeLang($language) {
        /**
         * @var $presenter \BasePresenter
         */
        $presenter = $this->getPresenter();
        $translator = $presenter->getTranslator();
        $translator->setLang($language);
        $presenter->redirect('this', ['lang' => $language]);
    }
}
