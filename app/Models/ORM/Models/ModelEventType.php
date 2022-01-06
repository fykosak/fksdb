<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\GroupedSelection;

/**
 * @property-read ActiveRow contest
 * @property-read int contest_id
 * @property-read int event_type_id
 */
class ModelEventType extends AbstractModel {

    public const FYZIKLANI = 1;

    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->contest);
    }

    public function getEventsByType(): GroupedSelection {
        return $this->related(DbNames::TAB_EVENT);
    }
}
