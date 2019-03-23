<?php

namespace FKSDB\ValidationTest;

use ModelEventParticipant;
use ModelPerson;
use Nette\Application\UI\Control;
use ParticipantsDurationComponent;

/**
 * Class ParticipantsDurationTest
 * @package FKSDB\ValidationTest
 */
class ParticipantsDurationTest extends ValidationTest {

    private $log = [];

    /**
     * @param ModelPerson $person
     * @return string|void
     */
    public function run(ModelPerson $person) {
        $max = null;
        $min = null;
        /**
         * @var $eventParticipant ModelEventParticipant
         */
        foreach ($person->getEventParticipant() as $eventParticipant) {

            $year = $eventParticipant->event->year;

            $max = (is_null($max) || $max < $year) ? $year : $max;
            $min = (is_null($min) || $min > $year) ? $year : $min;
        };
        $delta = $max - $min;

        $this->log[] = ['person' => $person, 'delta' => $delta, 'status' => ($delta < 4) ? 'success' : (($delta == 4) ? 'warning' : 'danger')];

    }

    /**
     * @return string
     */
    public function getAction(): string {
        return 'participantsDuration';
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('účasť na akciach');
    }

    /**
     * @return Control
     */
    public function getComponent(): Control {
        $component = new ParticipantsDurationComponent();
        $component->setLog($this->log);
        return $component;
    }
}
