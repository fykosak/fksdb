<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;

/**
 * @property-read int event_type_id
 * @property-read int contest_id
 * @property-read ContestModel contest
 * @property-read string name
 */
class EventTypeModel extends Model
{
    public function getEvents(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT, 'event_type_id');
    }

    public function getSymbol(): string
    {
        switch ($this->event_type_id) {
            case 1:
                return 'fof';
            case 9:
                return 'fol';
            case 2:
            case 14:
                return 'dsef';
            case 16:
                return 'fov';
            default:
                return 'secondary';
        }
    }
}
