<?php

namespace FKSDB\Components\Controls\Fyziklani;

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
        }
    }

    public function createComponentReactLogo() {
        return new ReactLogo();
    }
}
