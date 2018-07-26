<?php

namespace FKSDB\Components\Forms\Controls;

use FKS\Application\IJavaScriptCollector;
use Nette\ComponentModel\IComponent;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\BaseControl;

class PersonAccommodationMatrix extends BaseControl {
    const ID = 'person-accommodation-matrix';
    /**
     * @var bool
     */
    protected static $reactJSAttached = false;

    public function __construct() {
        parent::__construct();
        $this->setAttribute('data-id', self::ID);
    }

    public function setAccommodationDefinition($accommodationDef) {
        $this->setAttribute('data-accommodation-def', json_encode($accommodationDef));

    }

    /**
     * @param $obj IComponent
     */
    protected function attached($obj) {
        parent::attached($obj);
        Debugger::barDump('attached');
        Debugger::barDump($obj);
        if (!static::$reactJSAttached && $obj instanceof IJavaScriptCollector) {
            static::$reactJSAttached = true;
            $obj->registerJSFile('js/lib/react.min.js');
            $obj->registerJSFile('js/lib/react-dom.min.js');
            $obj->registerJSFile('js/bundle-accommodation.js');
        }
    }
}
