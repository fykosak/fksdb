<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @property-read int flag_id
 * @property-read ActiveRow flag
 * @property-read int value
 * @property-read \DateTimeInterface modified
 */
class ModelPersonHasFlag extends AbstractModel {

    public function getFlag(): ModelFlag {
        return ModelFlag::createFromActiveRow($this->flag);
    }
}
