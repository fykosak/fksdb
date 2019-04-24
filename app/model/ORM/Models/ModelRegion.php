<?php

namespace FKSDB\ORM\Models;
use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read integer region_id
 * @property-read string country_iso
 * @property-read string nuts
 * @property-read string name
 */
class ModelRegion extends AbstractModelSingle {

    const CZECH_REPUBLIC = 3;
    const SLOVAKIA = 2;

}
