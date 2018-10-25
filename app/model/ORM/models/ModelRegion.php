<?php

namespace FKSDB\ORM;
use AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property integer region_id
 * @property string country_iso
 * @property string nuts
 * @property string name
 */
class ModelRegion extends AbstractModelSingle {

    const CZECH_REPUBLIC = 3;
    const SLOVAKIA = 2;

}
