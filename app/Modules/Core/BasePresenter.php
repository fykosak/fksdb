<?php

namespace FKSDB\Modules\Core;

use FKSDB\Components\Controls\Breadcrumbs\BreadcrumbsComponent;
use FKSDB\Components\Controls\Choosers\LanguageChooserComponent;
use FKSDB\Components\Controls\Choosers\ThemeChooserComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
use FKSDB\Components\Controls\Loaders\StylesheetCollector;
use FKSDB\Components\Controls\Navigation\NavigablePresenter;
use FKSDB\Components\Controls\Navigation\NavigationChooserComponent;
use FKSDB\Components\Controls\Navigation\PresenterBuilder;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteJSONProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\FilteredDataProvider;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Localization\GettextTranslator;
use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Models\Logging\Logger;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\UI\PageStyleContainer;
use FKSDB\Models\UI\PageTitle;
use FKSDB\Models\Utils\Utils;
use FKSDB\Models\YearCalculator;
use FKSDB\Modules\Core\PresenterTraits\CollectorPresenterTrait;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\DI\Container;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter implements
    JavaScriptCollector,
    StylesheetCollector,
    AutocompleteJSONProvider,
    NavigablePresenter
{
    use CollectorPresenterTrait;

    public const FLASH_SUCCESS = Logger::SUCCESS;
    public const FLASH_INFO = Logger::INFO;
    public const FLASH_WARNING = Logger::WARNING;
    public const FLASH_ERROR = Logger::ERROR;

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
    protected YearCalculator $yearCalculator;
    protected ServiceContest $serviceContest;
    protected PresenterBuilder $presenterBuilder;
    protected GettextTranslator $translator;
    private ?PageTitle $pageTitle;
    private bool $authorized = true;
    private array $authorizedCache = [];
    private PageStyleContainer $pageStyleContainer;
    private Container $diContainer;

    final public function injectBase(
        Container $diContainer,
        YearCalculator $yearCalculator,
        ServiceContest $serviceContest,
        PresenterBuilder $presenterBuilder,
        GettextTranslator $translator
    ): void {
        $this->yearCalculator = $yearCalculator;
        $this->serviceContest = $serviceContest;
        $this->presenterBuilder = $presenterBuilder;
        $this->translator = $translator;
        $this->diContainer = $diContainer;
    }

    /**
     * @return void
     * @throws UnsupportedLanguageException
     */
    protected function startup(): void
    {
        parent::startup();
        /** @var LanguageChooserComponent $control */
        $control = $this->getComponent('languageChooser');
        $control->init();
    }

    protected function createTemplate(): Template
    {
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        return $template;
    }

    /*   * ******************************
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
            if ($provider && $provider instanceof FilteredDataProvider) {
                $data = $provider->getFilteredItems($acQ);
            }
            $response = new JsonResponse($data);
            $this->sendResponse($response);
        }
    }

    /*   * *******************************
     * Nette extension for navigation
     * ****************************** */

    /**
     * Formats title method name.
     * Method should set the title of the page using setTitle method.
     *
     * @param string
     * @return string
     */
    protected static function formatTitleMethod(string $view): string
    {
        return 'title' . $view;
    }

    public function setView(string $view): self
    {
        parent::setView($view);
        $this->pageTitle = null;
        return $this;
    }

    /**
     * @return PageTitle
     * @throws BadRequestException
     */
    public function getTitle(): PageTitle
    {
        if (!isset($this->pageTitle)) {
            $this->tryCall($this->formatTitleMethod($this->getView()), $this->params);
        }
        $this->pageTitle = $this->pageTitle ?? new PageTitle();
        $this->pageTitle->subTitle = $this->pageTitle->subTitle ?? $this->getDefaultSubTitle();
        return $this->pageTitle;
    }

    protected function setPageTitle(PageTitle $pageTitle): void
    {
        $this->pageTitle = $pageTitle;
    }

    protected function getDefaultSubTitle(): ?string
    {
        return null;
    }

    public function setBackLink(string $backLink): ?string
    {
        $old = $this->bc;
        $this->bc = $backLink;
        return $old;
    }

    public static function publicFormatActionMethod(string $action): string
    {
        return static::formatActionMethod($action);
    }

    public static function getBackLinkParamName(): string
    {
        return 'bc';
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     * @throws UnsupportedLanguageException
     */
    protected function beforeRender(): void
    {
        parent::beforeRender();

        $this->tryCall($this->formatTitleMethod($this->getView()), $this->params);
        $this->template->pageTitle = $this->getTitle();
        $this->template->pageStyleContainer = $this->getPageStyleContainer();
        $this->template->lang = $this->getLang();
        $this->template->navRoots = $this->getNavRoots();

        // this is done beforeRender, because earlier it would create too much traffic? due to redirections etc.
        $this->putIntoBreadcrumbs();
    }

    protected function getNavRoots(): array
    {
        return [];
    }

    final protected function getPageStyleContainer(): PageStyleContainer
    {
        $this->pageStyleContainer = $this->pageStyleContainer ?? new PageStyleContainer();
        return $this->pageStyleContainer;
    }

    /**
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    protected function putIntoBreadcrumbs(): void
    {
        /** @var BreadcrumbsComponent $component */
        $component = $this->getComponent('breadcrumbs');
        $component->setBackLink($this->getRequest());
    }

    protected function createComponentBreadcrumbs(): BreadcrumbsComponent
    {
        return new BreadcrumbsComponent($this->getContext());
    }

    protected function createComponentNavigationChooser(): NavigationChooserComponent
    {
        return new NavigationChooserComponent($this->getContext());
    }

    protected function createComponentThemeChooser(): ThemeChooserComponent
    {
        return new ThemeChooserComponent($this->getContext());
    }

    protected function createComponentValuePrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }

    protected function createComponentLinkPrinter(): LinkPrinterComponent
    {
        return new LinkPrinterComponent($this->getContext());
    }

    final protected function createComponentLanguageChooser(): LanguageChooserComponent
    {
        return new LanguageChooserComponent($this->getContext(), $this->lang);
    }

    /**
     * @return string
     * @throws UnsupportedLanguageException
     */
    public function getLang(): string
    {
        /** @var LanguageChooserComponent $control */
        $control = $this->getComponent('languageChooser');
        return $control->getLang();
    }

    /**
     * @param bool $need
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    final public function backLinkRedirect(bool $need = false): void
    {
        $this->putIntoBreadcrumbs();
        /** @var BreadcrumbsComponent $component */
        $component = $this->getComponent('breadcrumbs');
        $backLink = $component->getBackLinkUrl();
        if ($backLink) {
            $this->redirectUrl($backLink);
        } elseif ($need) {
            $this->redirect(':Core:Authentication:login'); // will cause dispatch
        }
    }

    /*   * *******************************
     * Extension of Nette ACL
     *      * ****************************** */

    public function isAuthorized(): bool
    {
        return $this->authorized;
    }

    public function setAuthorized(bool $access): void
    {
        $this->authorized = $access;
    }

    /**
     * @param mixed $element
     */
    public function checkRequirements($element): void
    {
        parent::checkRequirements($element);
        $this->setAuthorized(true);
    }

    /**
     * @param string $destination
     * @param array|null $args
     * @return bool
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
                $action = (string)substr($destination, $a + 1);
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
            /** @var BasePresenter $testedPresenter */
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

    public function getContext(): Container
    {
        return $this->diContainer;
    }
}
