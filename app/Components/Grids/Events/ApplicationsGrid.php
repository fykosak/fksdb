<?php

namespace FKSDB\Components\Events;

use Events\Machine\Machine;
use Events\Model\ApplicationHandler;
use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\IHolderSource;
use Events\Model\Holder\Holder;
use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Templating\ITemplate;
use Nette\Utils\Strings;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationsGrid extends Control {

    const NAME_PREFIX = 'application_';

    /**
     *
     * @var Container
     */
    private $container;

    /**
     * @var IHolderSource
     */
    private $source;

    /**
     * @var Holder[]
     */
    private $holders = [];

    /**
     * @var Machine[]
     */
    private $machines = [];

    /**
     * @var ModelEvent[]
     */
    private $eventApplications = [];

    /**
     * @var ApplicationHandler[]
     */
    private $handlers = [];

    /**
     * @var ApplicationHandlerFactory
     */
    private $handlerFactory;
    /**
     * @var string
     */
    private $templateFile;

    /**
     * @var boolean
     */
    private $searchable = false;

    /**
     * ApplicationsGrid constructor.
     * @param Container $container
     * @param IHolderSource $source
     * @param ApplicationHandlerFactory $handlerFactory
     */
    function __construct(Container $container, IHolderSource $source, ApplicationHandlerFactory $handlerFactory) {
        parent::__construct();
        $this->monitor(IJavaScriptCollector::class);
        $this->container = $container;
        $this->source = $source;
        $this->handlerFactory = $handlerFactory;
        $this->processSource();
    }

    private $attachedJS = false;

    /**
     * @param $obj
     */
    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedJS && $obj instanceof IJavaScriptCollector) {
            $this->attachedJS = true;
            $obj->registerJSFile('js/searchTable.js');
        }
    }

    /**
     * @param string $template name of the standard template or whole path
     */
    public function setTemplate($template) {
        if (stripos($template, '.latte') !== false) {
            $this->templateFile = $template;
        } else {
            $this->templateFile = __DIR__ . DIRECTORY_SEPARATOR . "ApplicationsGrid.$template.latte";
        }
    }

    /**
     * @return bool
     */
    public function isSearchable() {
        return $this->searchable;
    }

    /**
     * @param $searchable
     */
    public function setSearchable($searchable) {
        $this->searchable = $searchable;
    }

    private function processSource() {
        $this->eventApplications = [];
        foreach ($this->source as $key => $holder) {
            $this->eventApplications[$key] = $holder->getEvent();
            $this->holders[$key] = $holder;
            $this->machines[$key] = $this->container->createEventMachine($holder->getEvent());
            $this->handlers[$key] = $this->handlerFactory->create($holder->getEvent(), new MemoryLogger()); //TODO it's a bit weird to create new logger for each handler
        }
    }

    /**
     * @param $name
     * @return ApplicationComponent|IComponent
     */
    protected function createComponent($name) {

        $key = null;
        if (Strings::startsWith($name, self::NAME_PREFIX)) {
            $key = substr($name, strlen(self::NAME_PREFIX));
        }
        if (!$key) {
            parent::createComponent($name);
        }
        return new ApplicationComponent($this->handlers[$key], $this->holders[$key]);
    }

    /**
     * @param null $class
     * @return ITemplate
     */
    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->presenter->getTranslator());
        return $template;
    }

    public function render() {
        if (!$this->templateFile) {
            throw new InvalidStateException('Must set template for the grid.');
        }

        $this->template->eventApplications = $this->eventApplications;
        $this->template->holders = $this->holders;
        $this->template->machines = $this->machines;
        $this->template->htmlId = $this->lookupPath(Presenter::class);

        $this->template->setFile($this->templateFile);
        $this->template->render();
    }

}
