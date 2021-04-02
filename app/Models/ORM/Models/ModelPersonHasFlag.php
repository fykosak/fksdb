<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int flag_id
 * @property-read ActiveRow flag
 */
class ModelPersonHasFlag extends AbstractModel {

    public function getFlag(): ModelFlag {
        return ModelFlag::createFromActiveRow($this->flag);
    }
}
