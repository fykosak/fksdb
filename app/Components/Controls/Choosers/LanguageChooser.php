<?php

namespace FKSDB\Components\Controls\Choosers;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Http\Session;
use Nette\Templating\FileTemplate;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Jakub Šafin <xellos@fykos.cz>
 * @property FileTemplate $template
 */
class LanguageChooser extends Control {

    const DEFAULT_FIRST = 'cs';

    /**
     * @var array
     */
    private $supportedLanguages;

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
     * @var bool
     */
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
     * @return mixed
     */
    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    /**
     * @param $language
     * @return bool
     */
    public function isLanguage($language) {
        return in_array($language, $this->supportedLanguages);
    }

    /**
     * Redirect to correct address accorging to the resolved values.
     * @param string $lang
     * @throws \Exception
     */
    public function syncRedirect(string $lang = null) {
        $this->init($lang);
    }

    /**
     * @return mixed
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @param string $lang
     * @throws \Exception
     */
    private function init(string $lang = null) {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;
        if (count($this->getSupportedLanguages()) == 0) {
            return;
        }

        /* LANGUAGE */
        $this->language = $this->getDefaultLanguage();

        if ($lang !== null) {
            if (!$this->isLanguage($lang)) {
                $this->language = $this->getDefaultLanguage();
            } else {
                $this->language = $lang;
            }
        }
    }

    /**
     * @return array of existing languages
     * @throws \Exception
     */
    private function getSupportedLanguages(): array {
        if (!count($this->supportedLanguages)) {
            $presenter = $this->getPresenter();
            if (!($presenter instanceof \BasePresenter)) {
                throw new \Exception('Wrong presenter');
            }
            $this->supportedLanguages = $presenter->getTranslator()->getSupportedLanguages();
        }
        return $this->supportedLanguages;
    }

    /**
     * @param string|null $class
     * @throws \Exception
     */
    public function render(string $class = null) {

        $this->template->languages = $this->getSupportedLanguages();
        $this->template->languageNames = $this->languageNames;
        $this->template->currentLanguage = $this->getLanguage() ? $this->getLanguage() : null;
        $this->template->class = ($class !== null) ? $class : "nav navbar-nav navbar-right";
        $this->template->setTranslator($this->getPresenter()->getTranslator());

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'LanguageChooser.latte');
        $this->template->render();
    }

    /**
     * @param $language
     * @throws AbortException
     */
    public function handleChangeLang(string $language) {
        /**
         * @var \BasePresenter $presenter
         */
        $presenter = $this->getPresenter();
        $translator = $presenter->getTranslator();
        $translator->setLang($language);
        $presenter->redirect('this', ['lang' => $language]);
    }

    /**
     * @param string $lang
     * @return string
     * @throws BadRequestException
     */
    private function getLanguageName(string $lang): string {
        switch ($lang) {
            case'cs':
                return _('Čeština');
            case 'en' :
                return _('English');
            case'sk':
                return _('Slovenčina');
            default:
                throw new BadRequestException();
        }
    }
}
