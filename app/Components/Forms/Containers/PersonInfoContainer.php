<?php

namespace FKSDB\Components\Forms\Containers;

use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class PersonInfoContainer extends ModelContainer {

    /**
     * @param $values
     * @param bool $erase
     * @return \Nette\Forms\Container|void
     */
    public function setValues($values, $erase = FALSE) {
        if ($values instanceof ActiveRow) { //assert its from person info table
            $values['agreed'] = (bool) $values['agreed'];
        }

        parent::setValues($values, $erase);
    }

}
