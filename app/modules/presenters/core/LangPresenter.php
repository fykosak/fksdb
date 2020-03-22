<?php

namespace FKSDB;

use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Localization\GettextTranslator;
use Nette\Application\UI\Presenter;

/**
 * Class LangPresenter
 * @package FKSDB
 */
abstract class LangPresenter extends Presenter {

    /** @var GettextTranslator */
    private $translator;

    /**
     * @persistent
     * @internal
     */
    public $lang;

    /** @var string cache */
    private $_lang;

    /**
     * @param GettextTranslator $translator
     */
    public function injectTranslator(GettextTranslator $translator) {
        $this->translator = $translator;
    }

    /**
     * @throws \Exception
     */
    protected function startup() {
        parent::startup();
        $this->translator->setLang($this->getLang());
        /**
         * @var LanguageChooser $languageChooser
         */
        $languageChooser = $this->getComponent('languageChooser');
        $languageChooser->syncRedirect($this->getLang());
    }

    /**
     * @return LanguageChooser
     */
    protected final function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->session);
    }

    /**
     * Preferred language of the page
     *
     * @return string ISO 639-1
     * Should be final
     */
    public function getLang(): string {
        if (!$this->_lang) {
            $this->_lang = $this->lang;
            $supportedLanguages = $this->translator->getSupportedLanguages();
            if (!$this->_lang || !in_array($this->_lang, $supportedLanguages)) {
                $this->_lang = $this->getHttpRequest()->detectLanguage($supportedLanguages);
            }
            if (!$this->_lang) {
                $this->_lang = $this->globalParameters['localization']['defaultLanguage'];
            }
        }
        return $this->_lang;
    }

    /**
     * @return GettextTranslator
     */
    public final function getTranslator(): GettextTranslator {
        return $this->translator;
    }
}
