<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Localization\UnsupportedLanguageException;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\UI\Title;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Security\User;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Jakub Šafin <xellos@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 */
class LanguageChooser extends Chooser {

    private array $supportedLanguages = [];

    private string $language;

    public static array $languageNames = ['cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina'];

    private ?string $urlLang;

    private User $user;

    private IRequest $request;

    /**
     * LanguageChooser constructor.
     * @param Container $container
     * @param string|null $urlLang
     */
    public function __construct(Container $container, ?string $urlLang) {
        parent::__construct($container);
        $this->urlLang = $urlLang;
    }

    public function injectPrimary(User $user, IRequest $request): void {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * Preferred language of the page
     *
     * Should be final
     * @param bool $redirect
     * @throws AbortException
     * @throws UnsupportedLanguageException
     * @note do not call in constructor, call after component is attached
     */
    public function init(bool $redirect = true): void {
        if (!isset($this->language)) {
            $this->language = $this->selectLang();
            $this->getTranslator()->setLang($this->language);
        }
        if ($redirect && $this->urlLang !== $this->language) {
            $this->getPresenter()->redirect('this', ['lang' => $this->language]);
        }
    }

    /**
     * Preferred language of the page
     *
     * @param bool $redirect
     * @return string ISO 639-1
     * Should be final
     * @throws AbortException
     * @throws UnsupportedLanguageException
     */
    final public function getLang(bool $redirect = true): string {
        $this->init($redirect);
        return $this->language;
    }

    /**
     * @return string
     * @throws UnsupportedLanguageException
     */
    private function selectLang(): string {
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

    public function render(): void {
        $this->beforeRender();
        $this->template->modifiable = $this->isModifiable();
        $this->template->currentLanguageName = self::$languageNames[$this->language] ?: null;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.language.latte');
        $this->template->render();
    }

    private function getUserPreferredLang(): ?string {
        /**@var ModelLogin $login */
        $login = $this->user->getIdentity();
        if ($login && $login->getPerson()) {
            return $login->getPerson()->getPreferredLang();
        }
        return null;
    }

    private function isModifiable(): bool {
        return !$this->getUserPreferredLang();
    }

    /* ************ CHOOSER METHODS *************** */
    protected function getTitle(): Title {
        return new Title(isset(self::$languageNames[$this->language]) ? self::$languageNames[$this->language] : _('Language'), 'fa fa-language');
    }

    protected function getItems(): array {
        if (!count($this->supportedLanguages)) {
            $this->supportedLanguages = $this->getTranslator()->getSupportedLanguages();
        }
        return $this->supportedLanguages;
    }

    /**
     * @param string $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $this->language === $item;
    }

    /**
     * @param string $item
     * @return string
     */
    public function getItemLabel($item): string {
        return self::$languageNames[$item];
    }

    /**
     * @param string $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['lang' => $item]);
    }
}
