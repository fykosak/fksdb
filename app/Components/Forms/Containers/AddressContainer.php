<?php

namespace FKSDB\Components\Forms\Containers;

use AbstractModelMulti;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class AddressContainer extends ModelContainer {

    public function setValues($values, $erase = FALSE) {
        if ($values instanceof ActiveRow || $values instanceof AbstractModelMulti) { //assert its from address table
            if ($values instanceof AbstractModelMulti) {
                $address = $values->getMainModel();
            } else {
                $address = $values;
            }

            $values = $address->toArray();
            $values['country_iso'] = $address->region_id ? $address->region->country_iso : null;
        }

        parent::setValues($values, $erase);
    }

}
