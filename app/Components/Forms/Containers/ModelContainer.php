<?php

namespace FKSDB\Components\Forms\Containers;

use AbstractModelMulti;
use FKS\Components\Forms\Containers\ContainerWithOptions;
use Nette\Database\Table\ActiveRow;

/**
 * Formulářový kontejder reprezentující záznam z DB tabulky.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelContainer extends ContainerWithOptions {

    public function setValues($values, $erase = FALSE) {
        if ($values instanceof ActiveRow || $values instanceof AbstractModelMulti) {
            $values = $values->toArray();
        }
        parent::setValues($values, $erase);
    }

    public function setDisabled($value = true) {
        foreach ($this->getComponents() as $component) {
            $component->setDisabled($value);
        }
    }

}

?>
