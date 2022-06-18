<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int flag_id
 * @property-read ActiveRow flag
 * @property-read int value
 * @property-read \DateTimeInterface modified
 */
class ModelPersonHasFlag extends Model
{

    public function getFlag(): ModelFlag
    {
        return ModelFlag::createFromActiveRow($this->flag);
    }
}
