<?php

namespace FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;


abstract class Results extends ResultsAndStatistics {
    /**
     * @var bool
     */
    private static $JSAttached = false;

    protected function attached($obj) {
        parent::attached($obj);
        if (!static::$JSAttached && $obj instanceof IJavaScriptCollector) {
            static::$JSAttached = true;
            $obj->registerJSFile('js/tablesorter.min.js');
        }
    }

    protected final function getComponentName() {
        return 'results';
    }
}
