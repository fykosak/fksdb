<?php

namespace FKSDB\ValidationTest\Tests\ParticipantDuration;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;
use Nette\Database\Table\GroupedSelection;
use Nette\Database\Table\Selection;

/**
 * Class ParticipantsDurationTest
 * @package FKSDB\ValidationTest
 */
abstract class ParticipantsDuration extends ValidationTest {
    /**
     * @var ServiceContest
     */
    protected $serviceContest;

    /**
     * ParticipantsDuration constructor.
     * @param ServiceContest $serviceContest
     */
    public function __construct(ServiceContest $serviceContest) {
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param ModelPerson $person
     * @return GroupedSelection
     */
    protected function getEventParticipant(ModelPerson $person): Selection {
        return $person->getEventParticipant();
    }

    /**
     * @param ModelPerson $person
     * @return ValidationLog
     */
    public function run(ModelPerson $person): ValidationLog {
        $max = null;
        $min = null;
        foreach ($this->getEventParticipant($person) as $row) {
            $model = ModelEventParticipant::createFromActiveRow($row);
            $event = $model->getEvent();
            $contestId = $event->getEventType()->contest_id;
            if ($contestId !== $this->getContest()->contest_id) {
                continue;
            }
            $year = $event->year;

            $max = (is_null($max) || $max < $year) ? $year : $max;
            $min = (is_null($min) || $min > $year) ? $year : $min;
        };

        $delta = ($max - $min) + 1;
        return new ValidationLog(
            $this->getTitle(),
            \sprintf('Person participate %d years in the events of contest %s', $delta, $this->getContest()->name),
            ($delta < 5) ? ValidationLog::LVL_SUCCESS : (($delta < 6) ? ValidationLog::LVL_WARNING : ValidationLog::LVL_DANGER)
        );
    }

    /**
     * @return ModelContest
     */
    abstract protected function getContest(): ModelContest;

}
