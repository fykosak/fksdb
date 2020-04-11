<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read integer address_id
 * @property-read string postal_code
 * @property-read string city
 * @property-read ActiveRow region
 */
class ModelAddress extends AbstractModelSingle {

}
