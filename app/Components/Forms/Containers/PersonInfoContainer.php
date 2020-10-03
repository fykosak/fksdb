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
     * @param mixed|iterable $values
     * @param bool $erase
     * @return static
     */
    public function setValues($values, $erase = false): self {
        if ($values instanceof ActiveRow) { //assert its from person info table
            $values['agreed'] = (bool)$values['agreed'];
        }

        return parent::setValues($values, $erase);
    }

}
