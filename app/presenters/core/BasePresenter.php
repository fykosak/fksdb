<?php

use FKS\Components\Controls\JavaScriptLoader;
use FKS\Components\Controls\Navigation\BreadcrumbsFactory;
use FKS\Components\Controls\Navigation\INavigablePresenter;
use FKS\Components\Controls\StylesheetLoader;
use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider;
use Nette\Application\BadRequestException;
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
     * @var string|null
     */
    private $title;

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

    public function getTitle() {
        return $this->backlink();
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

    /*     * *******************************
     * Nette extension for ACL
     *      * ****************************** */

    /**
     * TODO remove?
     * Formats action method name.
     * @param  string
     * @return string
     */
    protected static function formatAccessMethod($action) {
        return 'access' . $action;
    }

}
