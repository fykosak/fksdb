<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @property-read int flag_id
 * @property-read ActiveRow flag
 */
class ModelPersonHasFlag extends AbstractModelSingle {

    public function getFlag(): ModelFlag {
        return ModelFlag::createFromActiveRow($this->flag);
    }
}
