<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Localization\GettextTranslator;
use FKSDB\Localization\UnsupportedLanguageException;
use FKSDB\ORM\Models\ModelLogin;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Security\User;

/**
 * Trait LangPresenterTrait
 * @author Michal Červeňák <miso@fykos.cz>
 * @method Container getContext()
 */
trait LangPresenterTrait {
    /** @var string[] */
    public static $languageNames = ['cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina'];

    private GettextTranslator $translator;

    /**
     * @persistent
     * @internal
     */
    public $lang;

    /** @var string cache */
    private $cacheLang;

    final public function injectTranslator(GettextTranslator $translator): void {
        $this->translator = $translator;
    }

    /**
     * @return void
     * @throws UnsupportedLanguageException
     */
    final protected function langTraitStartup(): void {
        $this->translator->setLang($this->getLang());
        /** @var LanguageChooser $languageChooser */
        $languageChooser = $this->getComponent('languageChooser');
        $languageChooser->setLang($this->getLang(), !$this->getUserPreferredLang());
    }

    final protected function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->getContext());
    }

    /**
     * @return string|null
     */
    final private function getUserPreferredLang() {
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
     * @throws UnsupportedLanguageException
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
                $this->cacheLang = $this->getContext()->getParameters()['localization']['defaultLanguage'];
            }
            // final check
            if (!in_array($this->cacheLang, $supportedLanguages)) {
                throw new UnsupportedLanguageException($this->cacheLang);
            }
        }
        return $this->cacheLang;
    }

    final public function getTranslator(): GettextTranslator {
        return $this->translator;
    }

    /**
     * @return User
     */
    abstract public function getUser();

    /**
     * @return Request
     */
    abstract public function getHttpRequest();
}
