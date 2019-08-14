<?php

namespace FKSDB\Components\Controls\Choosers;


use BasePresenter;
use Exception;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Http\Session;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;
use Nette\Templating\Template;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Jakub Šafin <xellos@fykos.cz>
 * @property FileTemplate $template
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

    /**
     * @return mixed
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * @return mixed
     */
    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    /**
     * @param $defaultLanguage
     */
    public function setDefaultLanguage($defaultLanguage) {
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * @param $language
     * @return bool
     */
    public function isLanguage($language) {
        return in_array($language, $this->languages);
    }

    /**
     * @param object $params
     * @return boolean
     * Redirect to correct address accorging to the resolved values.
     * @throws Exception
     */
    public function syncRedirect(&$params) {
        $this->init($params);

        if ($this->language !== $params->lang) {
            $params->lang = $this->language;

            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @param $params
     * @throws Exception
     */
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
     * @throws Exception
     */
    private function getSupportedLanguages() {
        $presenter = $this->getPresenter();
        if (!($presenter instanceof BasePresenter)) {
            throw new Exception('Wrong presenter');
        }
        return $presenter->getTranslator()->getSupportedLanguages();

    }

    /**
     * @param null $class
     * @return ITemplate|Template
     */
    protected function createTemplate($class = NULL) {
        /**
         * @var Template $template
         */
        $presenter = $this->getPresenter();

        $template = parent::createTemplate($class);
        if ($presenter instanceof BasePresenter) {
            $template->setTranslator($presenter->getTranslator());
        }
        return $template;
    }

    public function render() {

        $this->template->languages = $this->getLanguages();
        $this->template->languageNames = $this->languageNames;
        $this->template->currentLanguage = $this->getLanguage() ? $this->getLanguage() : null;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'LanguageChooser.latte');
        $this->template->render();
    }

    /**
     * @param $language
     * @throws AbortException
     */
    public function handleChangeLang($language) {
        /**
         * @var BasePresenter $presenter
         */
        $presenter = $this->getPresenter();
        $translator = $presenter->getTranslator();
        $translator->setLang($language);
        $presenter->redirect('this', ['lang' => $language]);
    }
}
