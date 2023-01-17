<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use FKSDB\Components\Controls\Choosers\LanguageChooserComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Components\Controls\Navigation\NavigationChooserComponent;
use FKSDB\Components\Controls\Navigation\PresenterBuilder;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteJSONProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\FilteredDataProvider;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\UI\PageStyleContainer;
use FKSDB\Models\Utils\Utils;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\DI\Container;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter implements AutocompleteJSONProvider
{
    /**
     * BackLink for tree construction for breadcrumbs.
     * @persistent
     */
    public ?string $bc = null;
    /**
     * @persistent
     * @internal
     */
    public ?string $lang = null;
    private string $language;
    protected ContestService $contestService;
    protected PresenterBuilder $presenterBuilder;
    protected GettextTranslator $translator;
    private ?PageTitle $pageTitle;
    private bool $authorized = true;
    private array $authorizedCache = [];
    private Container $diContainer;

    public static function publicFormatActionMethod(string $action): string
    {
        return static::formatActionMethod($action);
    }

    public static function getBackLinkParamName(): string
    {
        return 'bc';
    }

    final public function injectBase(
        Container $diContainer,
        ContestService $contestService,
        PresenterBuilder $presenterBuilder,
        GettextTranslator $translator
    ): void {
        $this->contestService = $contestService;
        $this->presenterBuilder = $presenterBuilder;
        $this->translator = $translator;
        $this->diContainer = $diContainer;
    }

    /* *******************************
     * IJSONProvider
     * ****************************** */

    public function handleAutocomplete(string $acName): void
    {
        ['acQ' => $acQ] = (array)json_decode($this->getHttpRequest()->getRawBody());
        $component = $this->getComponent($acName);
        if (!($component instanceof AutocompleteSelectBox)) {
            throw new \InvalidArgumentException('Cannot handle component of type ' . get_class($component) . '.');
        } else {
            $provider = $component->getDataProvider();
            $data = null;
            if ($provider instanceof FilteredDataProvider) {
                $data = $provider->getFilteredItems($acQ);
            }
            $response = new JsonResponse($data);
            $this->sendResponse($response);
        }
    }

    /* ********************************
     * Nette extension for navigation
     * ****************************** */

    public function setView(string $view): self
    {
        parent::setView($view);
        $this->pageTitle = null;
        return $this;
    }

    public function setBackLink(string $backLink): ?string
    {
        $old = $this->bc;
        $this->bc = $backLink;
        return $old;
    }

    /**
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function authorized(string $destination, ?array $args = null): bool
    {
        if (substr($destination, -1) === '!' || $destination === 'this') {
            $destination = $this->getAction(true);
        }

        $key = $destination . Utils::getFingerprint($args);
        if (!isset($this->authorizedCache[$key])) {
            /*
             * This part is extracted from Presenter::createRequest
             */
            $a = strrpos($destination, ':');
            if ($a === false) {
                $action = $destination;
                $presenter = $this->getName();
            } else {
                $action = substr($destination, $a + 1);
                if ($destination[0] === ':') { // absolute
                    if ($a < 2) {
                        throw new InvalidLinkException("Missing presenter name in '$destination'.");
                    }
                    $presenter = substr($destination, 1, $a - 1);
                } else { // relative
                    $presenter = $this->getName();
                    $b = strrpos($presenter, ':');
                    if ($b === false) { // no module
                        $presenter = substr($destination, 0, $a);
                    } else { // with module
                        $presenter = substr($presenter, 0, $b + 1) . substr($destination, 0, $a);
                    }
                }
            }

            /*
             * Now create a mock presenter and evaluate accessibility.
             */
            $baseParams = $this->getParameters();
            $testedPresenter = $this->presenterBuilder->preparePresenter($presenter, $action, $args, $baseParams);

            try {
                $testedPresenter->checkRequirements($testedPresenter->getReflection());
                $this->authorizedCache[$key] = $testedPresenter->isAuthorized();
            } catch (BadRequestException $exception) {
                $this->authorizedCache[$key] = false;
            }
        }
        return $this->authorizedCache[$key];
    }

    /**
     * @param mixed $element
     */
    public function checkRequirements($element): void
    {
        parent::checkRequirements($element);
        $this->setAuthorized(true);
    }

    public function isAuthorized(): bool
    {
        return $this->authorized;
    }

    public function setAuthorized(bool $access): void
    {
        $this->authorized = $access;
    }

    /**
     * @throws UnsupportedLanguageException
     */
    protected function startup(): void
    {
        parent::startup();
        if (!isset($this->language)) {
            $this->language = $this->selectLang();
            $this->translator->setLang($this->language);
        }
    }

    /**
     * @throws UnsupportedLanguageException
     */
    private function selectLang(): string
    {
        $candidate = $this->getUserPreferredLang() ?? $this->lang;
        $supportedLanguages = $this->translator->getSupportedLanguages();
        if (!$candidate || !in_array($candidate, $supportedLanguages)) {
            $candidate = $this->getHttpRequest()->detectLanguage($supportedLanguages);
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

    private function getUserPreferredLang(): ?string
    {
        $person = $this->getLoggedPerson();
        if ($person) {
            return $person->getPreferredLang();
        }
        return null;
    }


    protected function getLoggedPerson(): ?PersonModel
    {
        /**@var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        return $login ? $login->person : null;
    }

    protected function createTemplate(): Template
    {
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        return $template;
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();

        $this->template->pageTitle = $this->getTitle();
        $this->template->pageStyleContainer = $this->getPageStyleContainer();
        $this->template->lang = $this->getLang();
        $this->template->navRoots = $this->getNavRoots();
    }

    public function getTitle(): PageTitle
    {
        if (!isset($this->pageTitle)) {
            try {
                $reflection = new \ReflectionClass($this);
                $method = $reflection->getMethod('title' . $this->getView());
                $this->pageTitle = $method->invoke($this);
            } catch (\ReflectionException$exception) {
            }
        }
        $this->pageTitle = $this->pageTitle ?? new PageTitle(null, '');
        $this->pageTitle->subTitle = $this->pageTitle->subTitle ?? $this->getDefaultSubTitle();
        return $this->pageTitle;
    }

    protected function getDefaultSubTitle(): ?string
    {
        return null;
    }

    final protected function getPageStyleContainer(): PageStyleContainer
    {
        static $pageStyleContainer;
        if (!isset($pageStyleContainer)) {
            $pageStyleContainer = new PageStyleContainer();
        }
        return $pageStyleContainer;
    }

    public function getLang(): string
    {
        return $this->language;
    }

    protected function getNavRoots(): array
    {
        return [];
    }

    public function getContext(): Container
    {
        return $this->diContainer;
    }

    /*   * *******************************
     * Extension of Nette ACL
     *      * ****************************** */

    protected function createComponentNavigationChooser(): NavigationChooserComponent
    {
        return new NavigationChooserComponent($this->getContext());
    }

    protected function createComponentValuePrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }

    protected function createComponentLinkPrinter(): LinkPrinterComponent
    {
        return new LinkPrinterComponent($this->getContext());
    }

    final protected function createComponentLanguageChooser(): LanguageChooserComponent
    {
        return new LanguageChooserComponent($this->getContext(), $this->language, !$this->getUserPreferredLang());
    }
}
