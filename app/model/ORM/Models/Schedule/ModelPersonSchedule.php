<?php

namespace FKSDB\ORM\Models\Schedule;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\Transitions\IStateModel;
use Nette\Database\Table\ActiveRow;
use Nette\NotImplementedException;

/**
 * Class ModelPersonSchedule
 * @package FKSDB\ORM\Models\Schedule
 * @property-read ActiveRow person
 * @property-read ActiveRow schedule_item
 * @property-read int person_id
 * @property-read int schedule_item_id
 * @property-read string state
 * @property-read int person_schedule_id
 */
class ModelPersonSchedule extends AbstractModelSingle implements IStateModel, IPersonReferencedModel {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    /**
     * @return ModelScheduleItem
     */
    public function getScheduleItem(): ModelScheduleItem {
        return ModelScheduleItem::createFromActiveRow($this->schedule_item);
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

    /**
     * @return bool
     */
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
     */
    public function getLabel(): string {
        $item = $this->getScheduleItem();
        $group = $item->getGroup();
        $itemLabel = $item->getLabel();
        switch ($group->schedule_group_type) {
            case ModelScheduleGroup::TYPE_ACCOMMODATION:
                return \sprintf(_('%s: '),
                        $this->getPerson()->getFullName()
                    ) . $itemLabel;
            default:
                throw new NotImplementedException();
        }
    }

    /**
     * @param $newState
     * @return void
     */
    public function updateState($newState) {
        $this->update(['state' => $newState]);
    }

    /**
     * @return null|string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @return IStateModel
     * @throws NotImplementedException
     */
    public function refresh(): IStateModel {
        throw new NotImplementedException();
    }
}
