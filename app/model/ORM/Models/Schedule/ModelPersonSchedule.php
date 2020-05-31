<?php

namespace FKSDB\ORM\Models\Schedule;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\IPaymentReferencedModel;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\IScheduleGroupReferencedModel;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\Transitions\IStateModel;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\ActiveRow;
use FKSDB\Exceptions\NotImplementedException;

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
class ModelPersonSchedule extends AbstractModelSingle implements IStateModel, IPersonReferencedModel, IScheduleGroupReferencedModel, IPaymentReferencedModel {

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getScheduleItem(): ModelScheduleItem {
        return ModelScheduleItem::createFromActiveRow($this->schedule_item);
    }

    public function getScheduleGroup(): ModelScheduleGroup {
        return $this->getScheduleItem()->getScheduleGroup();
    }

    /**
     * @return ModelPayment|null
     */
    public function getPayment() {
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
                    $group->start->format(_('__date_format')),
                    $group->end->format(_('__date_format')),
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
     * @return $this
     * @throws NotImplementedException
     */
    public function refresh(Context $connection, IConventions $conventions): self {
        throw new NotImplementedException();
    }
}
