<?php

use Nette\Localization\ITranslator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DummyTranslator implements ITranslator {

    public function translate($message, $count = NULL) {
        return $message;
    }

}
