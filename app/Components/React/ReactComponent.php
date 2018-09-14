<?php

namespace FKSDB\Components\React;

use FKS\Application\IJavaScriptCollector;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\Templating\FileTemplate;

/**
 * Class ReactComponent
 * @property FileTemplate template
 */
abstract class ReactComponent extends Control {
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

    protected abstract function getComponentName();

    protected abstract function getModuleName();

    protected abstract function getMode();

    /**
     * @return string
     */
    protected abstract function getData();

    public final function render() {
        $this->template->moduleName = $this->getModuleName();
        $this->template->componentName = $this->getComponentName();
        $this->template->mode = $this->getMode();

        $this->template->data = $this->getData();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR.'ReactComponent.latte');
        $this->template->render();
    }
}
