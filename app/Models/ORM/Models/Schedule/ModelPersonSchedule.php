<?php

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\IEventReferencedModel;
use FKSDB\Models\ORM\Models\IPaymentReferencedModel;
use FKSDB\Models\ORM\Models\IPersonReferencedModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\Transitions\IStateModel;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\ActiveRow;

/**
 * Class ModelPersonSchedule
 * *
 * @property-read ActiveRow person
 * @property-read ActiveRow schedule_item
 * @property-read int person_id
 * @property-read int schedule_item_id
 * @property-read string state
 * @property-read int person_schedule_id
 */
class ModelPersonSchedule extends AbstractModelSingle implements
    IStateModel,
    IPersonReferencedModel,
    IScheduleGroupReferencedModel,
    IPaymentReferencedModel,
    IEventReferencedModel,
    IScheduleItemReferencedModel {

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

    /**
     * @param Context $connection
     * @param IConventions $conventions
     * @return IStateModel
     * @throws NotImplementedException
     */
    public function refresh(Context $connection, IConventions $conventions): IStateModel {
        throw new NotImplementedException();
    }
}
