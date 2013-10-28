<?php

use FKS\Components\Controls\JavaScriptLoader;
use FKS\Components\Controls\Navigation\BreadcrumbsFactory;
use FKS\Components\Controls\Navigation\INavigablePresenter;
use FKS\Components\Controls\Navigation\NavBar;
use FKS\Components\Controls\StylesheetLoader;
use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter implements IJavaScriptCollector, IStylesheetCollector, IAutocompleteJSONProvider, INavigablePresenter {

    const FLASH_SUCCESS = 'success';
    const FLASH_INFO = 'info';
    const FLASH_WARNING = 'warning';
    const FLASH_ERROR = 'danger';

    /** @persistent     */
    public $tld;

    /**
     * Backlink for tree construction for breadcrumbs.
     * 
     * @persistent
     */
    public $bc;

    /** @var YearCalculator  */
    protected $yearCalculator;

    /** @var ServiceContest */
    protected $serviceContest;

    /** @var BreadcrumbsFactory */
    private $breadcrumbsFactory;

    /**
     * @var NavBar
     */
    private $navigationControl;

    /**
     * @var string|null
     */
    private $title = false;

    /**
     * @var boolean
     */
    private $access = true;

    public function getYearCalculator() {
        return $this->yearCalculator;
    }

    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    public function getServiceContest() {
        return $this->serviceContest;
    }

    public function injectServiceContest(ServiceContest $serviceContest) {
        $this->serviceContest = $serviceContest;
    }

    public function injectBreadcrumbsFactory(BreadcrumbsFactory $breadcrumbsFactory) {
        $this->breadcrumbsFactory = $breadcrumbsFactory;
    }

    public function injectNavigationControl(NavBar $navigationControl) {
        $this->navigationControl = $navigationControl;
    }

    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator(new DummyTranslator());
        return $template;
    }

    /*     * ******************************
     * Loading assets
     * ****************************** */

    protected function createComponentJsLoader($name) {
        $component = new JavaScriptLoader();
        return $component;
    }

    protected function createComponentCssLoader($name) {
        $component = new StylesheetLoader();
        return $component;
    }

    /*     * ******************************
     * IJavaScriptCollector
     * ****************************** */

    public function registerJSFile($file) {
        $this['jsLoader']->addFile($file);
    }

    public function registerJSCode($code) {
        $this['jsLoader']->addInline($code);
    }

    /*     * ******************************
     * IStylesheetCollector
     * ****************************** */

    public function registerStylesheetFile($file, $media = array()) {
        $this['cssLoader']->addFile($file, $media);
    }

    /*     * ******************************
     * IJSONProvider
     * ****************************** */

    public function handleAutocomplete($acName, $acQ) {
        if (!$this->isAjax()) {
            throw new BadRequestException('Can be called only by AJAX.');
        }
        $component = $this->getComponent($acName);
        if (!($component instanceof AutocompleteSelectBox)) {
            throw new InvalidArgumentException('Cannot handle component of type ' . get_class($component) . '.');
        } else {
            $data = $component->getDataProvider()->getFilteredItems($acQ);
            $response = new JsonResponse($data);
            $this->sendResponse($response);
        }
    }

    /*     * *******************************
     * Nette extension for navigation 
     * ****************************** */

    /**
     * Formats title method name.
     * Method should set the title of the page using setTitle method.
     * 
     * @param  string
     * @return string
     */
    protected static function formatTitleMethod($view) {
        return 'title' . $view;
    }

    public function setView($view) {
        parent::setView($view);
        $method = $this->formatTitleMethod($this->getView());
        if (!$this->tryCall($method, $this->getParameter())) {
            $this->title = null;
        }
    }

    public function getTitle() {
        return $this->title;
    }

    protected function setTitle($title) {
        $this->title = $title;
    }

    public function setBacklink($backlink) {
        $old = $this->bc;
        $this->bc = $backlink;
        return $old;
    }

    public static function publicFormatActionMethod($action) {
        return static::formatActionMethod($action);
    }

    public static function getBacklinkParamName() {
        return 'bc';
    }

    protected function beforeRender() {
        parent::beforeRender();

        $this->tryCall($this->formatTitleMethod($this->getView()), $this->params);
        $this->template->title = $this->getTitle();

        // this is done beforeRender, because earlier it would create too much traffic? due to redirections etc.
        $this['breadcrumbs']->setBacklink($this->getRequest());
    }

    protected function createComponentBreadcrumbs($name) {
        $component = $this->breadcrumbsFactory->create();
        return $component;
    }

    protected function createComponentNavigation($name) {
        return $this->navigationControl;
    }

    /*     * *******************************
     * Nette extension for ACL
     *      * ****************************** */

    public function getAccess() {
        return $this->access;
    }

    public function setAccess($access) {
        $this->access = $access;
    }

    /**
     * TODO remove?
     * Formats action method name.
     * @param  string
     * @return string
     */
    protected static function formatAccessMethod($action) {
        return 'access' . $action;
    }

    public function checkRequirements($element) {
        parent::checkRequirements($element);
        if ($element instanceof ReflectionClass) {
            $method = $this->formatAccessMethod($this->getAction());
            if (!$this->tryCall($method, $this->getParameter())) {
                $this->access = true;
            }
        }
    }

}
