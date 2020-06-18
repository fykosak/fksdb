<?php

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Application\IStylesheetCollector;
use FKSDB\CollectorPresenterTrait;
use FKSDB\Components\Controls\Breadcrumbs\Breadcrumbs;
use FKSDB\Components\Controls\Breadcrumbs\BreadcrumbsFactory;
use FKSDB\Components\Controls\Navigation\INavigablePresenter;
use FKSDB\Components\Controls\Navigation\Navigation;
use FKSDB\Components\Controls\PresenterBuilder;
use FKSDB\Components\DatabaseReflection\DetailComponent;
use FKSDB\Components\DatabaseReflection\ValuePrinterComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\IFilteredDataProvider;
use FKSDB\Config\GlobalParameters;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\LangPresenterTrait;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\UI\PageStyleContainer;
use FKSDB\UI\PageTitle;
use FKSDB\YearCalculator;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 * @property ITemplate $template
 */
abstract class BasePresenter extends Presenter implements IJavaScriptCollector, IStylesheetCollector, IAutocompleteJSONProvider, INavigablePresenter {

    use CollectorPresenterTrait;
    use LangPresenterTrait;

    const FLASH_SUCCESS = ILogger::SUCCESS;

    const FLASH_INFO = ILogger::INFO;

    const FLASH_WARNING = ILogger::WARNING;

    const FLASH_ERROR = ILogger::ERROR;

    /** @persistent  */
    public $tld;

    /**
     * Backlink for tree construction for breadcrumbs.
     *
     * @persistent
     */
    public $bc;

    /** @var YearCalculator */
    private $yearCalculator;

    /** @var ServiceContest */
    private $serviceContest;

    /** @var GlobalParameters */
    protected $globalParameters;

    /** @var BreadcrumbsFactory */
    private $breadcrumbsFactory;

    /** @var Navigation */
    private $navigationControl;

    /** @var PresenterBuilder */
    private $presenterBuilder;

    /**
     * @var PageTitle|null
     */
    private $pageTitle;

    /**
     * @var bool
     */
    private $authorized = true;

    /**
     * @var bool[]
     */
    private $authorizedCache = [];

    /**
     * @var FullHttpRequest
     */
    private $fullRequest;

    public function getYearCalculator(): YearCalculator {
        return $this->yearCalculator;
    }

    /**
     * @param YearCalculator $yearCalculator
     * @return void
     */
    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    public function getServiceContest(): ServiceContest {
        return $this->serviceContest;
    }

    /**
     * @param ServiceContest $serviceContest
     * @return void
     */
    public function injectServiceContest(ServiceContest $serviceContest) {
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param GlobalParameters $globalParameters
     * @return void
     */
    public function injectGlobalParameters(GlobalParameters $globalParameters) {
        $this->globalParameters = $globalParameters;
    }

    /**
     * @param BreadcrumbsFactory $breadcrumbsFactory
     * @return void
     */
    public function injectBreadcrumbsFactory(BreadcrumbsFactory $breadcrumbsFactory) {
        $this->breadcrumbsFactory = $breadcrumbsFactory;
    }

    /**
     * @param Navigation $navigationControl
     * @return void
     */
    public function injectNavigationControl(Navigation $navigationControl) {
        $this->navigationControl = $navigationControl;
    }

    /**
     * @param PresenterBuilder $presenterBuilder
     * @return void
     */
    public function injectPresenterBuilder(PresenterBuilder $presenterBuilder) {
        $this->presenterBuilder = $presenterBuilder;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function startup() {
        parent::startup();
        $this->langTraitStartup();
    }

    /**
     * @return ITemplate
     */
    protected function createTemplate() {
        $template = parent::createTemplate();
        $template->setTranslator($this->getTranslator());
        return $template;
    }

    /*	 * ******************************
     * IJSONProvider
     * ****************************** */
    /**
     * @param $acName
     * @param $acQ
     * @throws BadRequestException
     * @throws AbortException
     */
    public function handleAutocomplete($acName, $acQ) {
        if (!$this->isAjax()) {
            throw new BadRequestException('Can be called only by AJAX.');
        }
        $component = $this->getComponent($acName);
        if (!($component instanceof AutocompleteSelectBox)) {
            throw new InvalidArgumentException('Cannot handle component of type ' . get_class($component) . '.');
        } else {
            $provider = $component->getDataProvider();
            $data = null;
            if ($provider instanceof IFilteredDataProvider) {
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
    protected static function formatTitleMethod($view) {
        return 'title' . $view;
    }

    /**
     * @param $view
     * @return static
     */
    public function setView($view) {
        parent::setView($view);
        $method = $this->formatTitleMethod($this->getView());
        if (!$this->tryCall($method, $this->getParameters())) {
            $this->pageTitle = null;
        }
        return $this;
    }

    public function getTitle(): PageTitle {
        return $this->pageTitle ?? new PageTitle();
    }

    /**
     * @param string $title
     * @param string $icon
     * @param string $subTitle
     * @return void
     */
    protected function setTitle(string $title, string $icon = '', string $subTitle = '') {
        $this->pageTitle = new PageTitle($title, $icon, $subTitle);
    }

    /**
     * @param string $backLink
     * @return null|string
     */
    public function setBackLink($backLink) {
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
     *
     * @throws BadRequestException
     * @throws ReflectionException
     */
    protected function beforeRender() {
        parent::beforeRender();

        $this->tryCall($this->formatTitleMethod($this->getView()), $this->params);
        $this->template->pageTitle = $this->getTitle();
        $this->template->pageStyleContainer = $this->getPageStyleContainer();
        $this->template->lang = $this->getLang();
        $this->template->navRoots = $this->getNavRoots();

        // this is done beforeRender, because earlier it would create too much traffic? due to redirections etc.
        $this->putIntoBreadcrumbs();
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array {
        return [];
    }

    protected function getPageStyleContainer(): PageStyleContainer {
        $container = new PageStyleContainer();
        $container->navBarClassName = 'bg-light navbar-light';
        $container->mainContainerClassName = 'container bg-white-container';
        return $container;
    }

    /**
     * @throws ReflectionException
     * @throws BadTypeException
     */
    protected function putIntoBreadcrumbs() {
        /** @var Breadcrumbs $component */
        $component = $this->getComponent('breadcrumbs');
        $component->setBackLink($this->getRequest());
    }

    protected function createComponentBreadcrumbs(): Breadcrumbs {
        return $this->breadcrumbsFactory->create();
    }

    protected function createComponentNavigation(): Navigation {
        $this->navigationControl->setParent();
        return $this->navigationControl;
    }

    protected function createComponentDetail(): DetailComponent {
        return new DetailComponent($this->getContext());
    }

    /**
     * @param bool $need
     * @throws AbortException
     * @throws BadTypeException
     * @throws ReflectionException
     */
    final public function backLinkRedirect($need = false) {
        $this->putIntoBreadcrumbs();
        /**
         * @var Breadcrumbs $component
         */
        $component = $this->getComponent('breadcrumbs');
        $backLink = $component->getBackLinkUrl();
        if ($backLink) {
            $this->redirectUrl($backLink);
        } elseif ($need) {
            $this->redirect(':Authentication:login'); // will cause dispatch
        }
    }

    /*	 * *******************************
     * Extension of Nette ACL
     *      * ****************************** */

    public function isAuthorized(): bool {
        return $this->authorized;
    }

    /**
     * @param bool $access
     * @return void
     */
    public function setAuthorized(bool $access) {
        $this->authorized = $access;
    }

    /**
     * @param $element
     * @throws ForbiddenRequestException
     */
    public function checkRequirements($element) {
        parent::checkRequirements($element);
        $this->setAuthorized(true);
    }

    /**
     * @param $destination
     * @param null $args
     * @return bool|mixed
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function authorized($destination, $args = null) {
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
                    if ($b === FALSE) { // no module
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
        if ($this->fullRequest === null) {
            $payload = file_get_contents('php://input');
            $this->fullRequest = new FullHttpRequest($this->getHttpRequest(), $payload);
        }
        return $this->fullRequest;
    }

    protected function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }
}
