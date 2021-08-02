<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Security\User;

class LanguageChooserComponent extends ChooserComponent
{

    private array $supportedLanguages = [];

    private string $language;

    public static array $languageNames = ['cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina'];

    private ?string $urlLang;

    private User $user;

    private IRequest $request;

    public function __construct(Container $container, ?string $urlLang)
    {
        parent::__construct($container);
        $this->urlLang = $urlLang;
    }

    final public function injectPrimary(User $user, IRequest $request): void
    {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * Preferred language of the page
     *
     * Should be final
     * @throws UnsupportedLanguageException
     * @note do not call in constructor, call after component is attached
     */
    public function init(): void
    {
        if (!isset($this->language)) {
            $this->language = $this->selectLang();
            $this->getTranslator()->setLang($this->language);
        }
        /*if ($redirect && $this->urlLang !== $this->language) {
              $this->getPresenter()->forward('this', ['lang' => $this->language]);
          }*/
    }

    /**
     * Preferred language of the page
     *
     * @return string ISO 639-1
     * @throws UnsupportedLanguageException
     */
    final public function getLang(): string
    {
        $this->init();
        return $this->language;
    }

    /**
     * @return string
     * @throws UnsupportedLanguageException
     */
    private function selectLang(): string
    {
        $candidate = $this->getUserPreferredLang() ?? $this->urlLang;
        $supportedLanguages = $this->getTranslator()->getSupportedLanguages();
        if (!$candidate || !in_array($candidate, $supportedLanguages)) {
            $candidate = $this->request->detectLanguage($supportedLanguages);
        }
        if (!$candidate) {
            $candidate = $this->getContext()->getParameters()['localization']['defaultLanguage'];
        }
        // final check
        if (!in_array($candidate, $supportedLanguages)) {
            throw new UnsupportedLanguageException($candidate);
        }
        return $candidate;
    }

    final public function render(): void
    {
        $this->beforeRender();
        $this->template->modifiable = $this->isModifiable();
        $this->template->currentLanguageName = self::$languageNames[$this->language] ?? null;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.language.latte');
    }

    private function getUserPreferredLang(): ?string
    {
        /**@var ModelLogin $login */
        $login = $this->user->getIdentity();
        if ($login && $login->getPerson()) {
            return $login->getPerson()->getPreferredLang();
        }
        return null;
    }

    private function isModifiable(): bool
    {
        return !$this->getUserPreferredLang();
    }

    /* ************ CHOOSER METHODS *************** */
    protected function getTitle(): Title
    {
        return new Title(isset(self::$languageNames[$this->language]) ? self::$languageNames[$this->language] : _('Language'), 'fa fa-language');
    }

    protected function getItems(): array
    {
        if (!count($this->supportedLanguages)) {
            $this->supportedLanguages = $this->getTranslator()->getSupportedLanguages();
        }
        return $this->supportedLanguages;
    }

    /**
     * @param string $item
     * @return bool
     */
    public function isItemActive($item): bool
    {
        return $this->language === $item;
    }

    /**
     * @param string $item
     * @return Title
     */
    public function getItemTitle($item): Title
    {
        return new Title(self::$languageNames[$item]);
    }

    /**
     * @param string $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string
    {
        return $this->getPresenter()->link('this', ['lang' => $item]);
    }
}
