<?php

namespace FKSDB\ValidationTest\Tests;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;

/**
 * Class ParticipantsDurationTest
 * @package FKSDB\ValidationTest
 */
class ParticipantsDuration extends ValidationTest {


    /**
     * @param ModelPerson $person
     * @return ValidationLog[]
     */
    public function run(ModelPerson $person): array {
        $data = [];
        $log = [];
        /**
         * @var ModelContest[] $contests
         */
        $contests = [];
        foreach ($person->getEventParticipant() as $row) {
            $model = ModelEventParticipant::createFromTableRow($row);
            $event = $model->getEvent();
            $contestId = $event->getEventType()->contest_id;
            $year = $event->year;
            if (!isset($data[$contestId])) {
                $contests[$contestId] = ModelContest::createFromTableRow($event->getEventType()->contest);
                $data[$contestId] = ['max' => null, 'min' => null];
            }

            $data[$contestId]['max'] = (is_null($data[$contestId]['max']) || $data[$contestId]['max'] < $year) ? $year : $data[$contestId]['max'];
            $data[$contestId]['min'] = (is_null($data[$contestId]['min']) || $data[$contestId]['min'] > $year) ? $year : $data[$contestId]['min'];
        };
        foreach ($data as $key => $value) {
            $delta = ($value['max'] - $value['min']) + 1;
            $log[] = new ValidationLog(self::getTitle(), \sprintf('Person participate %d years in the events of contest %s', $delta, $contests[$key]->name),
                ($delta < 5) ? self::LVL_SUCCESS : (($delta < 6) ? self::LVL_WARNING : self::LVL_DANGER));

        }
        return $log;
    }

    /**
     * @return string
     */
    public static function getAction(): string {
        return 'participants_duration';
    }

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Participate on events');
    }
}
