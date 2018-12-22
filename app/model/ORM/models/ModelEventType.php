<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property ActiveRow contest
 * @property integer contest_id
 */
class ModelEventType extends AbstractModelSingle {

    const FYZIKLANI = 1;

}
