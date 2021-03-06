<?php

namespace FKSDB\Components\Forms\Containers;

use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class PersonInfoContainer extends ModelContainer {

    /**
     * @param mixed|iterable $data
     * @param bool $erase
     * @return static
     */
    public function setValues($data, bool $erase = false): self {
        if ($data instanceof ActiveRow) { //assert its from person info table
            $data['agreed'] = (bool)$data['agreed'];
        }

        return parent::setValues($data, $erase);
    }

}
