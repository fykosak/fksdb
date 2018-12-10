<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use DbNames;
use Events\Model\Holder\Holder;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property integer event_year
 * @property integer year
 * @property string name
 * @property integer event_id
 * @property ActiveRow event_type
 * @property integer event_type_id
 */
class ModelEvent extends AbstractModelSingle implements IResource {

    /**
     * Event can have a holder assigned for purposes of parameter parsing.
     * Nothing else (currently).
     * @var Holder
     */
    private $holder;

    /**
     * @param Holder $holder
     */
    function setHolder(Holder $holder) {
        $this->holder = $holder;
    }

    /**
     * @return ModelEventType
     */
    public function getEventType(): ModelEventType {
        return ModelEventType::createFromTableRow($this->event_type);
    }

    /**
     * @return ModelEventAccommodation[]
     */
    public function getEventAccommodations(): array {
        $data = [];
        foreach ($this->related(DbNames::TAB_EVENT_ACCOMMODATION) as $item) {
            $data[] = ModelEventAccommodation::createFromTableRow($item);
        }
        return $data;
    }

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        return ModelContest::createFromTableRow($this->getEventType()->contest);
    }

    /**
     * Syntactic sugar.
     *
     * @return int
     */
    public function getAcYear(): int {
        return $this->getContest()->related('contest_year')->where('year', $this->year)->fetch()->ac_year;
    }

    /**
     * @param $name
     * @return mixed
     * @throws InvalidStateException
     */
    public function getParameter($name) {
        if (!$this->holder) {
            throw new InvalidStateException('Event does not have any holder assigned.');
        }
        return $this->holder->getParameter($name);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'event';
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->name;
    }

}
