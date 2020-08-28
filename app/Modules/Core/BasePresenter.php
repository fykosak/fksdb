<?php

namespace FKSDB\Modules\Core;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Application\IStylesheetCollector;
use FKSDB\Components\Controls\Breadcrumbs\Breadcrumbs;
use FKSDB\Components\Controls\Breadcrumbs\BreadcrumbsFactory;
use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Components\Controls\Choosers\ThemeChooser;
use FKSDB\Components\Controls\DBReflection\DetailComponent;
use FKSDB\Components\Controls\Navigation\INavigablePresenter;
use FKSDB\Components\Controls\Navigation\NavigationChooser;
use FKSDB\Components\Controls\PresenterBuilder;
use FKSDB\Components\Controls\DBReflection\ValuePrinterComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\IFilteredDataProvider;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Localization\GettextTranslator;
use FKSDB\Localization\UnsupportedLanguageException;
use FKSDB\Logging\ILogger;
use FKSDB\Modules\Core\PresenterTraits\CollectorPresenterTrait;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\UI\PageStyleContainer;
use FKSDB\UI\PageTitle;
use FKSDB\YearCalculator;
use FKSDB\FullHttpRequest;
use InvalidArgumentException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use ReflectionException;
use FKSDB\Utils\Utils;

/**
 * Base presenter for all application presenters.
 * @property ITemplate $template
 */
abstract class BasePresenter extends Presenter implements IJavaScriptCollector, IStylesheetCollector, IAutocompleteJSONProvider, INavigablePresenter {

    use CollectorPresenterTrait;

    public const FLASH_SUCCESS = ILogger::SUCCESS;

    public const FLASH_INFO = ILogger::INFO;

    public const FLASH_WARNING = ILogger::WARNING;

    public const FLASH_ERROR = ILogger::ERROR;

    /** @persistent  */
    public $tld;

    /**
     * BackLink for tree construction for breadcrumbs.
     *
     * @persistent
     */
    public $bc;

    /**
     * @persistent
     * @internal
     */
    public $lang;

    private YearCalculator $yearCalculator;

    private ServiceContest $serviceContest;

    private BreadcrumbsFactory $breadcrumbsFactory;

    private PresenterBuilder $presenterBuilder;

    private ?PageTitle $pageTitle;

    private bool $authorized = true;

    private array $authorizedCache = [];

    private FullHttpRequest $fullRequest;

    private PageStyleContainer $pageStyleContainer;

    private GettextTranslator $translator;

    public function getYearCalculator(): YearCalculator {
        return $this->yearCalculator;
    }

    public function injectYearCalculator(YearCalculator $yearCalculator): void {
        $this->yearCalculator = $yearCalculator;
    }

    public function getServiceContest(): ServiceContest {
        return $this->serviceContest;
    }

    public function injectServiceContest(ServiceContest $serviceContest): void {
        $this->serviceContest = $serviceContest;
    }

    public function injectBreadcrumbsFactory(BreadcrumbsFactory $breadcrumbsFactory): void {
        $this->breadcrumbsFactory = $breadcrumbsFactory;
    }

    public function injectPresenterBuilder(PresenterBuilder $presenterBuilder): void {
        $this->presenterBuilder = $presenterBuilder;
    }

    final public function injectTranslator(GettextTranslator $translator): void {
        $this->translator = $translator;
    }

    /**
     * @return void
     * @throws UnsupportedLanguageException
     * @throws AbortException
     */
    protected function startup() {
        parent::startup();
        /** @var LanguageChooser $control */
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
     * @param mixed|string $acName
     * @param mixed|string $acQ
     * @return void
     * @throws AbortException
     */
    public function handleAutocomplete($acName, $acQ): void {
        if (!$this->isAjax()) {
            ['acQ' => $acQ] = (array)json_decode($this->getHttpRequest()->getRawBody());
        }
        $component = $this->getComponent($acName);
        if (!($component instanceof AutocompleteSelectBox)) {
            throw new InvalidArgumentException('Cannot handle component of type ' . get_class($component) . '.');
        } else {
            $provider = $component->getDataProvider();
            $data = null;
            if ($provider && $provider instanceof IFilteredDataProvider) {
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
    protected static function formatTitleMethod($view): string {
        return 'title' . $view;
    }

    /**
     * @param string $view
     * @return static
     */
    public function setView($view): self {
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
     * @throws BadTypeException
     * @throws ReflectionException
     * @throws UnsupportedLanguageException
     * @throws AbortException
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
        /** @var Breadcrumbs $component */
        $component = $this->getComponent('breadcrumbs');
        $component->setBackLink($this->getRequest());
    }

    protected function createComponentBreadcrumbs(): Breadcrumbs {
        return $this->breadcrumbsFactory->create();
    }

    protected function createComponentNavigationChooser(): NavigationChooser {
        return new NavigationChooser($this->getContext());
    }

    protected function createComponentDetail(): DetailComponent {
        return new DetailComponent($this->getContext());
    }

    protected function createComponentThemeChooser(): ThemeChooser {
        return new ThemeChooser($this->getContext());
    }

    protected function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }

    final protected function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->getContext(), $this->lang);
    }

    /**
     * @return string
     * @throws UnsupportedLanguageException
     * @throws AbortException
     */
    public function getLang(): string {
        /** @var LanguageChooser $control */
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
        /** @var Breadcrumbs $component */
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


    /*	 * *******************************
     * Nette workaround
     *      * ****************************** */
    public function getFullHttpRequest(): FullHttpRequest {
        if (!isset($this->fullRequest)) {
            $payload = file_get_contents('php://input');
            $this->fullRequest = new FullHttpRequest($this->getHttpRequest(), $payload);
        }
        return $this->fullRequest;
    }
}
