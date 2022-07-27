<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\GroupedSelection;

/**
 * @property-read ModelContest contest
 * @property-read int contest_id
 * @property-read int event_type_id
 * @property-read string name
 */
class ModelEventType extends Model
{

    public const FYZIKLANI = 1;

    public function getEventsByType(): GroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT);
    }
}
