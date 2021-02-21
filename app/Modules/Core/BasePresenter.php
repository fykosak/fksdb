<?php

namespace FKSDB\Modules\Core;

use FKSDB\Components\Controls\Breadcrumbs\BreadcrumbsComponent;
use FKSDB\Components\Controls\Breadcrumbs\BreadcrumbsFactory;
use FKSDB\Components\Controls\Choosers\LanguageChooserComponent;
use FKSDB\Components\Controls\Choosers\ThemeChooserComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
use FKSDB\Components\Controls\Loaders\StylesheetCollector;
use FKSDB\Components\Controls\Navigation\NavigablePresenter;
use FKSDB\Components\Controls\Navigation\NavigationChooserComponent;
use FKSDB\Components\Controls\Navigation\PresenterBuilder;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteJSONProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\FilteredDataProvider;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Localization\GettextTranslator;
use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Models\Logging\Logger;
use FKSDB\Modules\Core\PresenterTraits\CollectorPresenterTrait;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\UI\PageStyleContainer;
use FKSDB\Models\UI\PageTitle;
use FKSDB\Models\YearCalculator;
use InvalidArgumentException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use ReflectionException;
use FKSDB\Models\Utils\Utils;

/**
 * Base presenter for all application presenters.
 * @property ITemplate $template
 */
abstract class BasePresenter extends Presenter implements JavaScriptCollector, StylesheetCollector, AutocompleteJSONProvider, NavigablePresenter {

    use CollectorPresenterTrait;

    public const FLASH_SUCCESS = Logger::SUCCESS;
    public const FLASH_INFO = Logger::INFO;
    public const FLASH_WARNING = Logger::WARNING;
    public const FLASH_ERROR = Logger::ERROR;
    /** @persistent */
    public ?string $tld = null;

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
    protected BreadcrumbsFactory $breadcrumbsFactory;
    protected PresenterBuilder $presenterBuilder;
    protected GettextTranslator $translator;
    private ?PageTitle $pageTitle;
    private bool $authorized = true;
    private array $authorizedCache = [];
    private PageStyleContainer $pageStyleContainer;

    final public function injectBase(
        YearCalculator $yearCalculator,
        ServiceContest $serviceContest,
        BreadcrumbsFactory $breadcrumbsFactory,
        PresenterBuilder $presenterBuilder,
        GettextTranslator $translator
    ): void {
        $this->yearCalculator = $yearCalculator;
        $this->serviceContest = $serviceContest;
        $this->breadcrumbsFactory = $breadcrumbsFactory;
        $this->presenterBuilder = $presenterBuilder;
        $this->translator = $translator;
    }

    /**
     * @return void
     * @throws UnsupportedLanguageException
     * @throws AbortException
     */
    protected function startup(): void {
        parent::startup();
        /** @var LanguageChooserComponent $control */
        $control = $this->getComponent('languageChooser');
        $control->init();
    }

    protected function createTemplate(): ITemplate {
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        return $template;
    }

    /*	 * ******************************
     * IJSONProvider
     * ****************************** */
    /**
     * @param string $acName
     * @return void
     * @throws AbortException
     */
    public function handleAutocomplete(string $acName): void {
        ['acQ' => $acQ] = (array)json_decode($this->getHttpRequest()->getRawBody());
        $component = $this->getComponent($acName);
        if (!($component instanceof AutocompleteSelectBox)) {
            throw new InvalidArgumentException('Cannot handle component of type ' . get_class($component) . '.');
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

    /*	 * *******************************
     * Nette extension for navigation
     * ****************************** */

    /**
     * Formats title method name.
     * Method should set the title of the page using setTitle method.
     *
     * @param string
     * @return string
     */
    protected static function formatTitleMethod(string $view): string {
        return 'title' . $view;
    }

    public function setView(string $view): self {
        parent::setView($view);
        $this->pageTitle = null;
        return $this;
    }

    private function callTitleMethod(): void {
        $method = $this->formatTitleMethod($this->getView());
        if (method_exists($this, $method)) {
            $this->{$method}();
            return;
        }
        $this->pageTitle = null;
    }

    public function getTitle(): PageTitle {
        if (!isset($this->pageTitle)) {
            $this->callTitleMethod();
        }
        return $this->pageTitle ?? new PageTitle();
    }

    protected function setPageTitle(PageTitle $pageTitle): void {
        $this->pageTitle = $pageTitle;
    }

    public function setBackLink(string $backLink): ?string {
        $old = $this->bc;
        $this->bc = $backLink;
        return $old;
    }

    public static function publicFormatActionMethod(string $action): string {
        return static::formatActionMethod($action);
    }

    public static function getBackLinkParamName(): string {
        return 'bc';
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws ReflectionException
     * @throws UnsupportedLanguageException
     */
    protected function beforeRender(): void {
        parent::beforeRender();

        $this->tryCall($this->formatTitleMethod($this->getView()), $this->params);
        $this->template->pageTitle = $this->getTitle();
        $this->template->pageStyleContainer = $this->getPageStyleContainer();
        $this->template->lang = $this->getLang();
        $this->template->navRoots = $this->getNavRoots();

        // this is done beforeRender, because earlier it would create too much traffic? due to redirections etc.
        $this->putIntoBreadcrumbs();
    }

    protected function getNavRoots(): array {
        return [];
    }

    final protected function getPageStyleContainer(): PageStyleContainer {
        $this->pageStyleContainer = $this->pageStyleContainer ?? new PageStyleContainer();
        return $this->pageStyleContainer;
    }

    /**
     * @throws ReflectionException
     * @throws BadTypeException
     */
    protected function putIntoBreadcrumbs(): void {
        /** @var BreadcrumbsComponent $component */
        $component = $this->getComponent('breadcrumbs');
        $component->setBackLink($this->getRequest());
    }

    protected function createComponentBreadcrumbs(): BreadcrumbsComponent {
        return $this->breadcrumbsFactory->create();
    }

    protected function createComponentNavigationChooser(): NavigationChooserComponent {
        return new NavigationChooserComponent($this->getContext());
    }

    protected function createComponentThemeChooser(): ThemeChooserComponent {
        return new ThemeChooserComponent($this->getContext());
    }

    protected function createComponentValuePrinter(): ColumnPrinterComponent {
        return new ColumnPrinterComponent($this->getContext());
    }

    protected function createComponentLinkPrinter(): LinkPrinterComponent {
        return new LinkPrinterComponent($this->getContext());
    }

    final protected function createComponentLanguageChooser(): LanguageChooserComponent {
        return new LanguageChooserComponent($this->getContext(), $this->lang);
    }

    /**
     * @return string
     * @throws UnsupportedLanguageException
     * @throws AbortException
     */
    public function getLang(): string {
        /** @var LanguageChooserComponent $control */
        $control = $this->getComponent('languageChooser');
        return $control->getLang();
    }

    /**
     * @param bool $need
     * @throws AbortException
     * @throws BadTypeException
     * @throws ReflectionException
     */
    final public function backLinkRedirect(bool $need = false): void {
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

    /*	 * *******************************
     * Extension of Nette ACL
     *      * ****************************** */

    public function isAuthorized(): bool {
        return $this->authorized;
    }

    public function setAuthorized(bool $access): void {
        $this->authorized = $access;
    }

    /**
     * @param mixed $element
     * @throws ForbiddenRequestException
     */
    public function checkRequirements($element): void {
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
    public function authorized(string $destination, $args = null): bool {
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
}
