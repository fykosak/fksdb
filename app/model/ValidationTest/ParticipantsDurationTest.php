<?php

class ParticipantsDurationTest extends ValidationTest {
    /**
     * @var ServicePerson
     */
    private $servicePerson;
    private $log = [];

    public function __construct(\ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function run() {
        $persons = $this->servicePerson->where('person_id<200');
        /**
         * @var $person ModelPerson
         */
        foreach ($persons as $row) {
            $person = ModelPerson::createFromTableRow($row);
            $max = null;
            $min = null;
            /**
             * @var $eventParticipant ModelEventParticipant
             */
            foreach ($person->getEventParticipant() as $eventParticipant) {

                $year = $eventParticipant->event->year;
                \Nette\Diagnostics\Debugger::barDump($year);
                $max = (is_null($max) || $max < $year) ? $year : $max;
                $min = (is_null($min) || $min > $year) ? $year : $min;
            };
            $delta = $max - $min;

            $this->log[] = ['person' => $person, 'delta' => $delta, 'status' => ($delta < 4) ? 'success' : (($delta == 4) ? 'warning' : 'danger')];

        }
        \Nette\Diagnostics\Debugger::barDump($this->log);
        // TODO: Implement run() method.
    }

    public function getAction() {
        return 'participantsDuration';
    }

    public function getTitle() {
        return _("účasť na eventoch");
    }

    public function getComponent() {
        $component = new ParticipantsDurationComponent();
        $component->setLog($this->log);
        return $component;
    }
}
