<?php

namespace FKSDB\ORM\Models;
use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readinteger region_id
 * @property-readstring country_iso
 * @property-readstring nuts
 * @property-readstring name
 */
class ModelRegion extends AbstractModelSingle {

    const CZECH_REPUBLIC = 3;
    const SLOVAKIA = 2;

}
