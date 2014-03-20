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
     * @var ModelLanguage
     */
    private $language;
    private $languageNames = array('cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina');

    /**
     * @var boolean
     */
    private $valid;
    private $initialized = false;

    /**
     * @var enum DEFAULT_*
     */
    private $defaultLanguage = self::DEFAULT_FIRST;

    /**
     * 

     * @param Session $session
     * @param ServiceLanguage $serviceLanguage
     */
    function __construct(Session $session) {
        $this->session = $session;
    }

    /**
     * @param mixed $languageDefinition role enum|ALL_LANGUAGES|array of languages
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

    public function isValid() {
        $this->init();
        return $this->valid;
    }

    /**
     * Redirect to correct address accorging to the resolved values.
     */
    public function syncRedirect() {
        $this->init();

        $presenter = $this->getPresenter();

        $language = $this->language;
        if ($language != $presenter->lang) {
            $presenter->redirect('this', array('lang' => $language));
        }
    }

    public function getLanguage() {
        $this->init();
        return $this->language;
    }

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
    
    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->getPresenter()->getTranslator());
        return $template;
    }

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

    public function handleChangeLang($language) {
        $presenter = $this->getPresenter();
        $translator = $presenter->getTranslator();

        $translator->setLang($language);

        $presenter->redirect('this', array('lang' => $language));
    }

}
