<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
use FKSDB\Components\Controls\Loaders\StylesheetCollector;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

class SQLConsole extends TextArea {

    protected const CSS_CLASS = 'sqlConsole';

    public function getControl(): Html {
        $control = parent::getControl();
        $control->class = self::CSS_CLASS;
        return $control;
    }

}
