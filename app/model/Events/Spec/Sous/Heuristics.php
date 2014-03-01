<?php

namespace Events\Spec\Sous;

use SQL\StoredQueryPostProcessing;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Heuristics extends StoredQueryPostProcessing {

    public function getDescription() {
        return _('Z výsledkovky vybere zvance a náhradníky na soustředění (http://wiki.fykos.cz/fykos:soustredeni:zasady:heuristikazvani).');
    }

    public function processData($data, $orderColumns, $offset, $limit) {
        $result = array();
        foreach ($data as $row) {
            $row['invited'] = time();
            $result[] = $row;
        }
        return $result;
    }

}
