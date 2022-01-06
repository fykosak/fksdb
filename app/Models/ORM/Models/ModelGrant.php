<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\AbstractModel;

/**
 * @property-read int contest_id
 * @property-read ActiveRow role
 * @property-read ActiveRow contest
 */
class ModelGrant extends AbstractModel
{

    public function getRole(): ModelRole
    {
        return ModelRole::createFromActiveRow($this->role);
    }

    public function getContest(): ModelContest
    {
        return ModelContest::createFromActiveRow($this->contest);
    }
}
