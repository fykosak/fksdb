<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\ORM\AbstractModelMulti;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Container;

/**
 * Formulářový kontejder reprezentující záznam z DB tabulky.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelContainer extends ContainerWithOptions {

    /**
     * @param $values
     * @param bool $erase
     * @return Container|void
     */
    public function setValues($values, $erase = FALSE) {
        if ($values instanceof ActiveRow || $values instanceof AbstractModelMulti) {
            $values = $values->toArray();
        }
        parent::setValues($values, $erase);
    }

    /**
     * @param bool $value
     */
    public function setDisabled($value = true) {
        foreach ($this->getComponents() as $component) {
            $component->setDisabled($value);
        }
    }

}
