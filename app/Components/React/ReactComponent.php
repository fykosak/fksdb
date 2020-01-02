<?php

namespace FKSDB\Components\React;

use FKSDB\Application\IJavaScriptCollector;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\NotImplementedException;
use Nette\Templating\FileTemplate;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class ReactComponent
 * @property FileTemplate template
 *
 */
abstract class ReactComponent extends Control {
    /**
     * @var string[]
     */
    private $actions = [];
    /**
     * @var Container
     */
    protected $container;

    /**
     * ReactComponent constructor.
     * @param Container $context
     */
    public function __construct(Container $context) {
        parent::__construct();
        $this->container = $context;
    }

    /**
     * @var bool
     */
    protected static $reactJSAttached = false;

    /**
     * @param IComponent $obj
     */
    protected function attached($obj) {
        if (!static::$reactJSAttached && $obj instanceof IJavaScriptCollector) {
            static::$reactJSAttached = true;
            $obj->registerJSFile('js/bundle.min.js');
        }
    }

    /**
     * @throws JsonException
     */
    public final function render() {
        $this->configure();
        $this->template->reactId = $this->getReactId();
        $this->template->actions = Json::encode($this->actions);
        $this->template->data = $this->getData();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReactComponent.latte');
        $this->template->render();
    }

    /**
     * @return void
     */
    protected function configure() {
    }

    /**
     * @param string $key
     * @param string $destination
     */
    public function addAction(string $key, string $destination) {
        $this->actions[$key] = $destination;
    }

    /**
     * @return IRequest
     */
    protected function getHttpRequest(): IRequest {
        return $this->container->getByType(IRequest::class);
    }

    /**
     * @return object
     */
    protected function getReactRequest() {

        $requestData = $this->getHttpRequest()->getPost('requestData');
        $act = $this->getHttpRequest()->getPost('act');
        return (object)['requestData' => $requestData, 'act' => $act];
    }

    /**
     * @return string
     */
    abstract protected function getReactId(): string;

    /**
     * @return string
     */
    abstract function getData(): string;
}
