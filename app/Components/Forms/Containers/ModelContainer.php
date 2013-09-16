<?php

namespace FKSDB\Components\Forms\Containers;

use Nette\Database\Table\ActiveRow;
use Nette\Forms\Container;

/**
 * Formulářový kontejder reprezentující záznam z DB tabulky.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */ 
class ModelContainer extends Container {

    public function setDefaults($values, $erase = FALSE) {
        if ($values instanceof ActiveRow) {
            $values = $values->toArray();
        }
        parent::setDefaults($values, $erase);
    }

}

?>
