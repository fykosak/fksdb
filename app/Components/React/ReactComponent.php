<?php

namespace FKSDB\Components\React;

use FKSDB\Application\IJavaScriptCollector;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
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
     * @param $obj IComponent
     */
    protected function attached($obj) {
        if (!static::$reactJSAttached && $obj instanceof IJavaScriptCollector) {
            static::$reactJSAttached = true;
            $obj->registerJSFile('js/lib/react.min.js');
            $obj->registerJSFile('js/lib/react-dom.min.js');
            $obj->registerJSFile('js/bundle-all.min.js');
        }
    }

    public final function render() {
        $this->template->moduleName = $this->getModuleName();
        $this->template->componentName = $this->getComponentName();
        $this->template->mode = $this->getMode();
        $this->template->actions = Json::encode($this->getActions());

        $this->template->data = $this->getData();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReactComponent.latte');
        $this->template->render();
    }

    protected function getActions() {
        return [];
    }

    /**
     * @return object
     */
    protected function getReactRequest() {
        $requestData = $this->getPresenter()->getHttpRequest()->getPost('requestData');
        $act = $this->getPresenter()->getHttpRequest()->getPost('act');
        return (object)['requestData' => $requestData, 'act' => $act];
    }
}
