<?php

namespace FKSDB\Components\React;

use FKSDB\Application\IJavaScriptCollector;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Templating\FileTemplate;
use Nette\Utils\Json;

/**
 * Class ReactComponent
 * @property FileTemplate template
 */
abstract class ReactComponent extends Control implements IReactComponent {
    /**
     * @var bool
     */
    protected static $reactJSAttached = false;
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
     * @param IComponent $obj
     */
    protected function attached($obj) {
        if (!static::$reactJSAttached && $obj instanceof IJavaScriptCollector) {
            static::$reactJSAttached = true;
            $obj->registerJSFile('js/tablesorter.min.js');
            $obj->registerJSFile('js/lib/react.min.js');
            $obj->registerJSFile('js/lib/react-dom.min.js');
            $obj->registerJSFile('js/bundle-all.min.js');
        }
    }

    /**
     * @throws \Nette\Utils\JsonException
     */
    public final function render() {
        $this->template->moduleName = $this->getModuleName();
        $this->template->componentName = $this->getComponentName();
        $this->template->mode = $this->getMode();
        $this->template->actions = Json::encode($this->getActions());

        $this->template->data = $this->getData();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReactComponent.latte');
        $this->template->render();
    }

    /**
     * @return array
     */
    public function getActions(): array {
        return [];
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
}
