<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\DeprecatedLazyDBTrait;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventType;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelEvent createNewModel(array $data)
 * @method ModelEvent|null findByPrimary($key)
 * @method ModelEvent refresh(AbstractModelSingle $model)
 */
class ServiceEvent extends AbstractServiceSingle {

    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_EVENT, ModelEvent::class);
    }

    public function getEvents(ModelContest $contest, int $year): TypedTableSelection {
        return $this->getTable()
            ->select(DbNames::TAB_EVENT . '.*')
            ->select(DbNames::TAB_EVENT_TYPE . '.name AS `type_name`')
            ->where(DbNames::TAB_EVENT_TYPE . '.contest_id', $contest->contest_id)
            ->where(DbNames::TAB_EVENT . '.year', $year);
    }

    public function getByEventTypeId(ModelContest $contest, int $year, int $eventTypeId): ?ModelEvent {
        /** @var ModelEvent $event */
        $event = $this->getEvents($contest, $year)->where(DbNames::TAB_EVENT . '.event_type_id', $eventTypeId)->fetch();
        return $event ?: null;
    }

    public function getEventsByType(ModelEventType $eventType): TypedTableSelection {
        return $this->getTable()->where('event_type_id', $eventType->event_type_id);
    }

    public function store(?ModelEvent $model, array $data): ModelEvent {
        if (is_null($model)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($model, $data);
            return $this->refresh($model);
        }
    }
}
