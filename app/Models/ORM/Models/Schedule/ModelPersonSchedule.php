<?php

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

/**
 * Class ModelPersonSchedule
 * @property-read ActiveRow person
 * @property-read ActiveRow schedule_item
 * @property-read int person_id
 * @property-read int schedule_item_id
 * @property-read string state
 * @property-read int person_schedule_id
 */
class ModelPersonSchedule extends AbstractModelSingle {

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getScheduleItem(): ModelScheduleItem {
        return ModelScheduleItem::createFromActiveRow($this->schedule_item);
    }

    public function getScheduleGroup(): ModelScheduleGroup {
        return $this->getScheduleItem()->getScheduleGroup();
    }

    public function getEvent(): ModelEvent {
        return $this->getScheduleGroup()->getEvent();
    }

    public function getPayment(): ?ModelPayment {
        $data = $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'person_schedule_id')->select('payment.*')->fetch();
        if (!$data) {
            return null;
        }
        return ModelPayment::createFromActiveRow($data);
    }

    public function hasActivePayment(): bool {
        $payment = $this->getPayment();
        if (!$payment) {
            return false;
        }
        if ($payment->getState() == ModelPayment::STATE_CANCELED) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     * @throws NotImplementedException
     */
    public function getLabel(): string {
        $item = $this->getScheduleItem();
        $group = $item->getScheduleGroup();
        switch ($group->schedule_group_type) {
            case ModelScheduleGroup::TYPE_ACCOMMODATION:
                return sprintf(_('Accommodation for %s from %s to %s in %s'),
                    $this->getPerson()->getFullName(),
                    $group->start->format(_('__date')),
                    $group->end->format(_('__date')),
                    $item->name_cs);
            case ModelScheduleGroup::TYPE_WEEKEND:
                return $item->getLabel();
            default:
                throw new NotImplementedException();
        }
    }

    public function updateState(?string $newState): void {
        $this->update(['state' => $newState]);
    }

    public function getState(): ?string {
        return $this->state;
    }
}
