<?php

namespace FKSDB;

use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Localization\GettextTranslator;
use FKSDB\ORM\Models\ModelLogin;
use Nette\Application\BadRequestException;
use Nette\Http\Request;
use Nette\Security\User;

/**
 * Class LangPresenter
 * @package FKSDB
 */
trait LangPresenterTrait {
    /** @var string[] */
    public static $languageNames = ['cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina'];

    /** @var GettextTranslator */
    private $translator;

    /**
     * @persistent
     * @internal
     */
    public $lang;

    /** @var string cache */
    private $cacheLang;

    /**
     * @param GettextTranslator $translator
     */
    public final function injectTranslator(GettextTranslator $translator) {
        $this->translator = $translator;
    }

    /**
     * @throws \Exception
     */
    protected final function langTraitStartup() {
        $this->translator->setLang($this->getLang());
        /** @var LanguageChooser $languageChooser */
        $languageChooser = $this->getComponent('languageChooser');
        $languageChooser->setLang($this->getLang(), !$this->getUserPreferredLang());
    }

    /**
     * @return LanguageChooser
     */
    protected final function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->getContext());
    }

    /**
     * @return string|null
     */
    private final function getUserPreferredLang() {
        /**@var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        if ($login && $login->getPerson()) {
            return $login->getPerson()->getPreferredLang();
        }
        return null;
    }

    /**
     * Preferred language of the page
     *
     * @return string ISO 639-1
     * Should be final
     * @throws BadRequestException
     */
    public function getLang(): string {
        if (!$this->cacheLang) {
            $this->cacheLang = $this->getUserPreferredLang();
            if (!$this->cacheLang) {
                $this->cacheLang = $this->lang;
            }
            $supportedLanguages = $this->translator->getSupportedLanguages();
            if (!$this->cacheLang || !in_array($this->cacheLang, $supportedLanguages)) {
                $this->cacheLang = $this->getHttpRequest()->detectLanguage($supportedLanguages);
            }
            if (!$this->cacheLang) {
                $this->cacheLang = $this->globalParameters['localization']['defaultLanguage'];
            }
            // final check
            if (!in_array($this->cacheLang, $supportedLanguages)) {
                throw new BadRequestException();
            }
        }
        return $this->cacheLang;
    }

    /**
     * @return GettextTranslator
     */
    public final function getTranslator(): GettextTranslator {
        return $this->translator;
    }

    /**
     * @return User
     */
    public abstract function getUser();

    /**
     * @return Request
     */
    public abstract function getHttpRequest();
}
