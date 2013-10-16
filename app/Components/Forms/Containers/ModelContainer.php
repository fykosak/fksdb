<?php

namespace FKSDB\Components\Forms\Containers;

use AbstractModelMulti;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Container;

/**
 * Formulářový kontejder reprezentující záznam z DB tabulky.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */ 
class ModelContainer extends Container {

    public function setValues($values, $erase = FALSE) {
        if ($values instanceof ActiveRow|| $values instanceof AbstractModelMulti) {
            $values = $values->toArray();
        }
        parent::setValues($values, $erase);
    }

}

?>
