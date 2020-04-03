<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\LangPresenterTrait;
use Nette\Application\AbortException;
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
    /** @var array */
    private $supportedLanguages;

    /** @var Session */
    private $session;

    /** @var string */
    private $language;

    /** @var bool */
    private $initialized = false;

    /** @var string */
    const DEFAULT_LANGUAGE = 'cs';
    /** @var bool */
    private $modifiable = true;

    /**
     * @param Session $session
     * @param bool $modifiable
     */
    function __construct(Session $session, bool $modifiable) {
        parent::__construct();
        $this->session = $session;
        $this->modifiable = $modifiable;
    }

    /**
     * @param string $language
     * @return bool
     */
    private function isLanguage(string $language): bool {
        return in_array($language, $this->supportedLanguages);
    }

    /**
     * Redirect to correct address accorging to the resolved values.
     * @param string $lang
     * @throws \Exception
     */
    public function setLang(string $lang) {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;
        if (count($this->getSupportedLanguages()) == 0) {
            return;
        }
        $this->language = self::DEFAULT_LANGUAGE;
        if ($this->isLanguage($lang)) {
            $this->language = $lang;
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
        $this->template->modifiable = $this->modifiable;
        $this->template->languages = $this->getSupportedLanguages();
        $this->template->languageNames = LangPresenterTrait::$languageNames;
        $this->template->currentLanguage = $this->language ?: null;
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
        /** @var \BasePresenter $presenter */
        $presenter = $this->getPresenter();
        $translator = $presenter->getTranslator();
        $translator->setLang($language);
        $presenter->redirect('this', ['lang' => $language]);
    }
}
