<?php

use FKS\Application\IJavaScriptCollector;
use FKS\Application\IStylesheetCollector;
use FKS\Components\Controls\JavaScriptLoader;
use FKS\Components\Controls\Navigation\BreadcrumbsFactory;
use FKS\Components\Controls\PresenterBuilder;
use FKS\Components\Controls\StylesheetLoader;
use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider;
use FKS\Config\GlobalParameters;
use FKS\Localization\GettextTranslator;
use FKSDB\Components\Controls\Navigation\INavigablePresenter;
use FKSDB\Components\Controls\Navigation\Navigation;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter implements IJavaScriptCollector, IStylesheetCollector, IAutocompleteJSONProvider, INavigablePresenter {

    const FLASH_SUCCESS = 'success';

    const FLASH_INFO = 'info';

    const FLASH_WARNING = 'warning';

    const FLASH_ERROR = 'danger';

    /** @persistent  */
    public $tld;

    /** @persistent */
    public $lang;

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
     * @var GettextTranslator
     */
    protected $translator;

    /**
     * @var string|null
     */
    protected $title = false;
    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var boolean
     */
    private $authorized = true;

    /**
     * @var array[string] => bool
     */
    private $authorizedCache = array();

    /**
     * @var string cache
     */
    private $_lang;

    /**
     * @var FullHttpRequest
     */
    private $fullRequest;

    /**
     * @var string
     */
    private $subtitle;

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

    public function injectGlobalParameters(GlobalParameters $globalParameters) {
        $this->globalParameters = $globalParameters;
    }

    public function injectBreadcrumbsFactory(BreadcrumbsFactory $breadcrumbsFactory) {
        $this->breadcrumbsFactory = $breadcrumbsFactory;
    }

    public function injectNavigationControl(Navigation $navigationControl) {
        $this->navigationControl = $navigationControl;
    }

    public function injectPresenterBuilder(PresenterBuilder $presenterBuilder) {
        $this->presenterBuilder = $presenterBuilder;
    }

    public function injectTranslator(GettextTranslator $translator) {
        $this->translator = $translator;
    }

    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->translator);
        $template->beta = $this->globalParameters['beta'];
        return $template;
    }

    protected function startup() {
        parent::startup();
        $this->translator->setLang($this->getLang());
    }

    /*	 * ******************************
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

    /*	 * ******************************
     * IJavaScriptCollector
     * ****************************** */

    public function registerJSFile($file) {
        $this['jsLoader']->addFile($file);
    }

    public function registerJSCode($code, $tag = null) {
        $this['jsLoader']->addInline($code, $tag);
    }

    public function unregisterJSCode($tag) {
        $this['jsLoader']->removeInline($tag);
    }

    public function unregisterJSFile($file) {
        $this['jsLoader']->removeFile($file);
    }

    /*	 * ******************************
     * IStylesheetCollector
     * ****************************** */

    public function registerStylesheetFile($file, $media = array()) {
        $this['cssLoader']->addFile($file, $media);
    }

    public function unregisterStylesheetFile($file, $media = array()) {
        $this['cssLoader']->removeFile($file, $media);
    }

    /*	 * ******************************
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

    /*	 * *******************************
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

    public function getIcon() {
        return $this->icon;
    }

    protected function setIcon($icon) {
        $this->icon = $icon;
    }

    protected function setSubtitle($subtitle) {
        $this->subtitle = $subtitle;
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

        list ($symbol, $type) = $this->getNavBarVariant();
        $this->template->contestSymbol = $symbol;
        $this->template->navVariant = $type;

        $this->template->subtitle = $this->getSubtitle();
        $this->template->icon = $this->getIcon();
        $this->template->navRoot = $this->getNavRoot();

        // this is done beforeRender, because earlier it would create too much traffic? due to redirections etc.
        $this->putIntoBreadcrumbs();
    }

    public function getNavRoot(){
        return null;
    }

    /**
     * @return array
     */
    protected function getNavBarVariant() {
        return [null, null];
    }

    protected function getSubtitle() {
        return $this->subtitle;
    }

    protected function putIntoBreadcrumbs() {
        $this['breadcrumbs']->setBacklink($this->getRequest());
    }

    protected function createComponentBreadcrumbs($name) {
        $component = $this->breadcrumbsFactory->create();
        return $component;
    }

    protected function createComponentNavigation($name) {
        $this->navigationControl->setParent();
        return $this->navigationControl;
    }

    public final function backlinkRedirect($need = false) {
        $this->putIntoBreadcrumbs();
        $backlink = $this['breadcrumbs']->getBacklinkUrl();
        if ($backlink) {
            $this->redirectUrl($backlink);
        } else if ($need) {
            $this->redirect(':Authentication:login'); // will cause dispatch
        }
    }

    /*	 * *******************************
     * Extension of Nette ACL
     *      * ****************************** */

    public function isAuthorized() {
        return $this->authorized;
    }

    public function setAuthorized($access) {
        $this->authorized = $access;
    }

    public function checkRequirements($element) {
        parent::checkRequirements($element);
        $this->setAuthorized(true);
    }

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
            $testedPresenter = $this->presenterBuilder->preparePresenter($presenter, $action, $args, $baseParams);
            try {
                $testedPresenter->checkRequirements($testedPresenter->getReflection());
                $this->authorizedCache[$key] = $testedPresenter->isAuthorized();
            } catch (BadRequestException $e) {
                $this->authorizedCache[$key] = false;
            }
        }
        return $this->authorizedCache[$key];
    }

    /*	 * *******************************
     * I18n
     *      * ****************************** */

    /**
     * Preferred language of the page
     *
     * @return string ISO 639-1
     */
    public function getLang() {
        if (!$this->_lang) {
            $this->_lang = $this->lang;
            $supportedLanguages = $this->translator->getSupportedLanguages();
            if (!$this->_lang || !in_array($this->_lang, $supportedLanguages)) {
                $this->_lang = $this->getHttpRequest()->detectLanguage($supportedLanguages);
            }
            if (!$this->_lang) {
                $this->_lang = $this->globalParameters['localization']['defaultLanguage'];
            }
        }
        return $this->_lang;
    }

    public function getTranslator() {
        return $this->translator;
    }

    /*	 * *******************************
     * Nette workaround
     *      * ****************************** */
    function getFullHttpRequest() {
        if ($this->fullRequest === null) {
            $payload = file_get_contents('php://input');
            $this->fullRequest = new FullHttpRequest($this->getHttpRequest(), $payload);
        }
        return $this->fullRequest;
    }


}
