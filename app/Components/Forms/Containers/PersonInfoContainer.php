<?php

namespace FKSDB\Components\Forms\Containers;

use Nette\Database\Table\ActiveRow;
use Nette\Forms\Container;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class PersonInfoContainer extends ModelContainer {

    /**
     * @param $values
     * @param bool $erase
     * @return Container|void
     */
    public function setValues($values, $erase = false) {
        if ($values instanceof ActiveRow) { //assert its from person info table
            $values['agreed'] = (bool)$values['agreed'];
        }
        parent::setValues($values, $erase);
    }

}
