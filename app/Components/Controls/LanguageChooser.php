<?php

namespace FKSDB\Components\Controls;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Http\Session;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Jakub Šafin <xellos@fykos.cz>
 */
class LanguageChooser extends Control {
//    const SESSION_PREFIX = 'contestPreset';

    const ALL_LANGS = '__*';
    const DEFAULT_FIRST = 'cs';
    const DEFAULT_NULL = 'null';

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

    /**
     * @var boolean
     */
    private $valid;
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var string
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
     * @param $languages
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
     * @return string
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
     * @return bool
     * @throws BadRequestException
     */
    public function isValid() {
        $this->init();
        return $this->valid;
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     *  Redirect to correct address accorging to the resolved values.
     */
    public function syncRedirect() {
        $this->init();

        $presenter = $this->getPresenter();

        $language = $this->language;
        if ($language != $presenter->lang && $language != self::DEFAULT_FIRST) {
            $presenter->redirect('this', array('lang' => $language));
        }
    }

    /**
     * @return mixed
     * @throws BadRequestException
     */
    public function getLanguage() {
        $this->init();
        return $this->language;
    }

    /**
     * @throws BadRequestException
     */
    private function init() {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $this->setLanguages($this->getSupportedLanguages());

        $languageIds = array_keys($this->getLanguages());
        if (count($languageIds) == 0) {
            $this->valid = false;
            return;
        }
        $this->valid = true;

        $presenter = $this->getPresenter();

        /* LANGUAGE */

        $this->language = $this->defaultLanguage;

        if ($presenter->getParameter('lang') != null) {
            if (!$this->isLanguage($presenter->getParameter('lang')))
                throw new BadRequestException('Bad language in URL.', 404);
            $this->language = $presenter->getParameter('lang');
        }
    }

    /**
     * @return array of existing languages
     */
    private function getSupportedLanguages() {
        return $this->getPresenter()->getTranslator()->getSupportedLanguages();
    }

    /**
     * @param null $class
     * @return \Nette\Templating\ITemplate
     */
    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->getPresenter()->getTranslator());
        return $template;
    }

    /**
     * @param $class
     * @throws BadRequestException
     */
    public function render($class) {
        if (!$this->isValid()) {
            throw new BadRequestException('No languages available.', 404);
        }
        $this->template->languages = $this->getLanguages();
        $this->template->languageNames = $this->languageNames;
        $this->template->currentLanguage = $this->getLanguage() ? $this->getLanguage() : null;
        $this->template->class = ($class !== null) ? $class : "nav navbar-nav navbar-right";

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'LanguageChooser.latte');
        $this->template->render();
    }

    /**
     * @param $language
     * @throws \Nette\Application\AbortException
     */
    public function handleChangeLang($language) {
        $presenter = $this->getPresenter();
        $translator = $presenter->getTranslator();

        $translator->setLang($language);

        $presenter->redirect('this', array('lang' => $language));
    }

}
