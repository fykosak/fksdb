<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\TextArea;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SQLConsole extends TextArea {

    const CSS_CLASS = 'sqlConsole';

    public function getControl() {
        $control = parent::getControl();
        $control->class = self::CSS_CLASS;

        return $control;
    }

}
