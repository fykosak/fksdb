<?php

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Application\IStylesheetCollector;
use FKSDB\Components\Controls\Breadcrumbs\Breadcrumbs;
use FKSDB\Components\Controls\Breadcrumbs\BreadcrumbsFactory;
use FKSDB\Components\Controls\Loaders\JavaScript\JavaScriptLoader;
use FKSDB\Components\Controls\Loaders\Stylesheet\StylesheetLoader;
use FKSDB\Components\Controls\Navigation\INavigablePresenter;
use FKSDB\Components\Controls\Navigation\Navigation;
use FKSDB\Components\Controls\PresenterBuilder;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\IFilteredDataProvider;
use FKSDB\Config\GlobalParameters;
use FKSDB\Localization\GettextTranslator;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Base presenter for all application presenters.
 * @property FileTemplate $template
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
     * @var bool
     */
    private $authorized = true;

    /**
     * @var array[string] => bool
     */
    private $authorizedCache = [];

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

    public function getYearCalculator(): YearCalculator {
        return $this->yearCalculator;
    }

    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    public function getServiceContest(): ServiceContest {
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

    /**
     * @param null $class
     * @return FileTemplate|\Nette\Templating\ITemplate
     */
    protected function createTemplate($class = NULL) {
        /**
         * @var $template FileTemplate
         */
        $template = parent::createTemplate($class);
        $template->setTranslator($this->translator);

        return $template;
    }


    protected function startup() {
        parent::startup();
        $this->translator->setLang($this->getLang());
    }

    /*	 * ******************************
     * Loading assets
     * ****************************** */

    protected function createComponentJsLoader(): JavaScriptLoader {
        return new JavaScriptLoader();
    }

    protected function createComponentCssLoader(): StylesheetLoader {
        return new StylesheetLoader();
    }

    /*	 * ******************************
     * IJavaScriptCollector
     * ****************************** */
    /**
     * @param string $file
     */
    public function registerJSFile($file) {
        /**
         * @var $component JavaScriptLoader
         */
        $component = $this->getComponent('jsLoader');
        $component->addFile($file);
    }

    /**
     * @param string $code
     * @param null $tag
     */
    public function registerJSCode($code, $tag = null) {
        /**
         * @var $component JavaScriptLoader
         */
        $component = $this->getComponent('jsLoader');
        $component->addInline($code, $tag);
    }

    /**
     * @param string $tag
     */
    public function unregisterJSCode($tag) {
        /**
         * @var $component JavaScriptLoader
         */
        $component = $this->getComponent('jsLoader');
        $component->removeInline($tag);
    }

    /**
     * @param string $file
     */
    public function unregisterJSFile($file) {
        /**
         * @var $component JavaScriptLoader
         */
        $component = $this->getComponent('jsLoader');
        $component->removeFile($file);
    }

    /*	 * ******************************
     * IStylesheetCollector
     * ****************************** */
    /**
     * @param string $file
     * @param array $media
     */
    public function registerStylesheetFile($file, $media = []) {
        /**
         * @var $component StylesheetLoader
         */
        $component = $this->getComponent('cssLoader');
        $component->addFile($file, $media);
    }

    /**
     * @param string $file
     * @param array $media
     */
    public function unregisterStylesheetFile($file, $media = []) {
        /**
         * @var $component StylesheetLoader
         */
        $component = $ $this->getComponent('cssLoader');
        $component->removeFile($file, $media);
    }

    /*	 * ******************************
     * IJSONProvider
     * ****************************** */
    /**
     * @param $acName
     * @param $acQ
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
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
     * @param  string
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
    public static function getBacklinkParamName(): string {
        return 'bc';
    }

    /**
     *
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

    protected function putIntoBreadcrumbs() {
        /**
         * @var $component Breadcrumbs
         */
        $component =  $this->getComponent('breadcrumbs');
        $component->setBacklink($this->getRequest());
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
     * @param bool $need
     * @throws \Nette\Application\AbortException
     */
    public final function backLinkRedirect($need = false) {
        $this->putIntoBreadcrumbs();
        /**
         * @var $component Breadcrumbs
         */
        $component =  $this->getComponent('breadcrumbs');
        $backLink = $component->getBacklinkUrl();
        if ($backLink) {
            $this->redirectUrl($backLink);
        } else if ($need) {
            $this->redirect(':Authentication:login'); // will cause dispatch
        }
    }

    /*	 * *******************************
     * Extension of Nette ACL
     *      * ****************************** */

    public function isAuthorized(): bool {
        return $this->authorized;
    }

    public function setAuthorized(bool $access) {
        $this->authorized = $access;
    }

    /**
     * @param $element
     * @throws \Nette\Application\ForbiddenRequestException
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

    public function getTranslator(): ITranslator {
        return $this->translator;
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


}
