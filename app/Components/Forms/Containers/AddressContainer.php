<?php

namespace FKSDB\Components\Forms\Containers;

use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class AddressContainer extends ModelContainer {

    public function setDefaults($values, $erase = FALSE) {
        if ($values instanceof ActiveRow) { //assert its from address table
            $address = $values;
            $values = $address->toArray();
            $values['country_iso'] = $address->region_id ? $address->region->country_iso : null;
        }

        parent::setDefaults($values, $erase);
    }

}
