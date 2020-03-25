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
use FKSDB\Components\DatabaseReflection\DetailFactory;
use FKSDB\Components\DatabaseReflection\ValuePrinterComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\IFilteredDataProvider;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Config\GlobalParameters;
use FKSDB\LangPresenterTrait;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\YearCalculator;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;

/**
 * Base presenter for all application presenters.
 * @property FileTemplate $template
 */
abstract class BasePresenter extends Presenter implements IAutocompleteJSONProvider, INavigablePresenter, IJavaScriptCollector, IStylesheetCollector {
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
    protected $yearCalculator;

    /** @var ServiceContest */
    protected $serviceContest;

    /** @var GlobalParameters */
    protected $globalParameters;

    /** @var BreadcrumbsFactory */
    private $breadcrumbsFactory;

    /** @var Navigation */
    private $navigationControl;

    /** @var PresenterBuilder */
    private $presenterBuilder;

    /**
     * @var string|null
     */
    protected $title = false;
    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var bool
     */
    private $authorized = true;

    /**
     * @var array[string] => bool
     */
    private $authorizedCache = [];

    /**
     * @var FullHttpRequest
     */
    private $fullRequest;

    /**
     * @var string
     */
    private $subtitle;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;
    /**
     * @var
     */
    private $detailFactory;

    /**
     * @return YearCalculator
     */
    public function getYearCalculator(): YearCalculator {
        return $this->yearCalculator;
    }

    /**
     * @param YearCalculator $yearCalculator
     */
    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param DetailFactory $detailFactory
     */
    public function injectDetailFactory(DetailFactory $detailFactory) {
        $this->detailFactory = $detailFactory;
    }

    /**
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public final function injectTableReflectionFactory(TableReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @return TableReflectionFactory
     */
    public final function getTableReflectionFactory(): TableReflectionFactory {
        return $this->tableReflectionFactory;
    }

    /**
     * @return ServiceContest
     */
    public function getServiceContest(): ServiceContest {
        return $this->serviceContest;
    }

    /**
     * @param ServiceContest $serviceContest
     */
    public function injectServiceContest(ServiceContest $serviceContest) {
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param GlobalParameters $globalParameters
     */
    public function injectGlobalParameters(GlobalParameters $globalParameters) {
        $this->globalParameters = $globalParameters;
    }

    /**
     * @param BreadcrumbsFactory $breadcrumbsFactory
     */
    public function injectBreadcrumbsFactory(BreadcrumbsFactory $breadcrumbsFactory) {
        $this->breadcrumbsFactory = $breadcrumbsFactory;
    }

    /**
     * @param Navigation $navigationControl
     */
    public function injectNavigationControl(Navigation $navigationControl) {
        $this->navigationControl = $navigationControl;
    }

    /**
     * @param PresenterBuilder $presenterBuilder
     */
    public function injectPresenterBuilder(PresenterBuilder $presenterBuilder) {
        $this->presenterBuilder = $presenterBuilder;
    }

    /**
     * @throws Exception
     */
    protected function startup() {
        parent::startup();
        $this->langTraitStartup();
    }


    /**
     * @param null $class
     * @return FileTemplate|ITemplate
     */
    protected function createTemplate($class = NULL) {
        /**
         * @var FileTemplate $template
         */
        $template = parent::createTemplate($class);
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
     * @return Presenter|void
     */
    public function setView($view) {
        parent::setView($view);
        $method = $this->formatTitleMethod($this->getView());
        if (!$this->tryCall($method, $this->getParameter())) {
            $this->title = null;
        }
    }

    /**
     * @return null|string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     * @param string $icon
     */
    protected function setTitle(string $title, string $icon = null) {
        $this->title = $title;
        if ($icon) {
            $this->icon = $icon;
        }
    }

    /**
     * @return string
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * @param $icon
     * @deprecated
     */
    protected function setIcon($icon) {
        $this->icon = $icon;
    }

    /**
     * @param $subtitle
     */
    protected function setSubtitle(string $subtitle = null) {
        $this->subtitle = $subtitle;
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

    /**
     * @param string $action
     * @return string
     */
    public static function publicFormatActionMethod($action) {
        return static::formatActionMethod($action);
    }

    /**
     * @return string
     */
    public static function getBackLinkParamName(): string {
        return 'bc';
    }

    /**
     *
     * @throws ReflectionException
     */
    protected function beforeRender() {
        parent::beforeRender();

        $this->tryCall($this->formatTitleMethod($this->getView()), $this->params);
        $this->template->title = $this->getTitle();

        list ($symbol, $type) = $this->getNavBarVariant();
        $this->template->contestSymbol = $symbol;
        $this->template->navbarClass = $type;

        $this->template->subtitle = $this->getSubtitle();
        $this->template->icon = $this->getIcon();
        $this->template->navRoots = $this->getNavRoots();

        // this is done beforeRender, because earlier it would create too much traffic? due to redirections etc.
        $this->putIntoBreadcrumbs();
    }


    /**
     * @return array
     */
    protected function getNavRoots(): array {
        return [];
    }

    /**
     * @return array
     */
    protected function getNavBarVariant(): array {
        return [null, null];
    }

    /**
     * @return string
     */
    public function getSubtitle(): string {
        return $this->subtitle ?: '';
    }

    /**
     * @throws ReflectionException
     */
    protected function putIntoBreadcrumbs() {
        /**
         * @var Breadcrumbs $component
         */
        $component = $this->getComponent('breadcrumbs');
        $component->setBackLink($this->getRequest());
    }

    /**
     * @return Breadcrumbs
     */
    protected function createComponentBreadcrumbs(): Breadcrumbs {
        return $this->breadcrumbsFactory->create();
    }

    /**
     * @return Navigation
     */
    protected function createComponentNavigation(): Navigation {
        $this->navigationControl->setParent();
        return $this->navigationControl;
    }

    /**
     * @return DetailComponent
     */
    protected function createComponentDetail(): DetailComponent {
        return new DetailComponent($this->detailFactory, $this->getTableReflectionFactory(), $this->getTranslator());
    }

    /**
     * @param bool $need
     * @throws ReflectionException
     * @throws AbortException
     */
    public final function backLinkRedirect($need = false) {
        $this->putIntoBreadcrumbs();
        /**
         * @var Breadcrumbs $component
         */
        $component = $this->getComponent('breadcrumbs');
        $backLink = $component->getBackLinkUrl();
        if ($backLink) {
            $this->redirectUrl($backLink);
        } else if ($need) {
            $this->redirect(':Authentication:login'); // will cause dispatch
        }
    }

    /*	 * *******************************
     * Extension of Nette ACL
     *      * ****************************** */

    /**
     * @return bool
     */
    public function isAuthorized(): bool {
        return $this->authorized;
    }

    /**
     * @param bool $access
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
     * @return mixed
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
            $baseParams = $this->getParameter();
            /**
             * @var BasePresenter $testedPresenter
             */
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
    /**
     * @return FullHttpRequest
     */
    function getFullHttpRequest(): FullHttpRequest {
        if ($this->fullRequest === null) {
            $payload = file_get_contents('php://input');
            $this->fullRequest = new FullHttpRequest($this->getHttpRequest(), $payload);
        }
        return $this->fullRequest;
    }

    /**
     * @return ValuePrinterComponent
     * @throws \Exception
     */
    public function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getTranslator(), $this->getTableReflectionFactory());
    }
}
