<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\GroupedSelection;

/**
 * @property-read int event_type_id
 * @property-read int contest_id
 * @property-read ContestModel contest
 * @property-read string name
 */
class EventTypeModel extends Model
{
    public const FYZIKLANI = 1;

    public function getEvents(): GroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT);
    }
}
