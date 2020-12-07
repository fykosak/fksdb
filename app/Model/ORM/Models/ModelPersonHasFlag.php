<?php

namespace FKSDB\Model\ORM\Models;

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
