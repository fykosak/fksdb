<?php

use Events\Model\Holder\Holder;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelEvent extends AbstractModelSingle implements IResource {

    private $eventType = false;

    private $contest = false;

    private $acYear = false;

    /**
     * Event can have a holder assigned for purposes of parameter parsing. 
     * Nothing else (currently).
     * @var Holder
     */
    private $holder;

    function setHolder(Holder $holder) {
        $this->holder = $holder;
    }

    public function getEventType() {
        if ($this->eventType === false) {
            $this->eventType = ModelEventType::createFromTableRow($this->ref(DbNames::TAB_EVENT_TYPE, 'event_type_id'));
        }
        return $this->eventType;
    }

    /**
     * @return ModelContest
     */
    public function getContest() {
        if ($this->contest === false) {
            $this->contest = ModelContest::createFromTableRow($this->getEventType()->ref(DbNames::TAB_CONTEST, 'contest_id'));
        }
        return $this->contest;
    }

    /**
     * Syntactic sugar.
     * 
     * @return int
     */
    public function getAcYear() {
        if ($this->acYear === false) {
            $this->acYear = $this->getContest()->related('contest_year')->where('year', $this->year)->fetch()->ac_year;
        }
        return $this->acYear;
    }

    public function getParameter($name) {
        if (!$this->holder) {
            throw new InvalidStateException('Event does not have any holder assigned.');
        }
        return $this->holder->getParameter($name);
    }

    public function getResourceId() {
        return 'event';
    }

    public function __toString() {
        return $this->name;
    }

}

?>
