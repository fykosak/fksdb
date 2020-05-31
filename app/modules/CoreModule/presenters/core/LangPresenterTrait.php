<?php

namespace FKSDB;

use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Localization\GettextTranslator;
use FKSDB\ORM\Models\ModelLogin;
use Nette\Application\BadRequestException;
use Nette\Http\Request;
use Nette\Localization\ITranslator;
use Nette\Security\User;

/**
 * Class LangPresenter
 * *
 */
trait LangPresenterTrait {
    /** @var string[] */
    public static array $languageNames = ['cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina'];

    private ITranslator $translator;

    /**
     * @persistent
     * @internal
     */
    public ?string $lang = null;

    /** @var string cache */
    private ?string $cacheLang = null;

    final public function injectTranslator(GettextTranslator $translator): void {
        $this->translator = $translator;
    }

    /**
     * @throws \Exception
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

    final public function getTranslator(): ITranslator {
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
