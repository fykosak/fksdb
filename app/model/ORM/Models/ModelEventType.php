<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readActiveRow contest
 * @property-readinteger contest_id
 */
class ModelEventType extends AbstractModelSingle {

    const FYZIKLANI = 1;

}
