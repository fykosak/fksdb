<?php

namespace FKSDB\ValidationTest;

use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class EventCoveringTest
 * @package FKSDB\ValidationTest
 */
class EventCoveringValidation extends ValidationTest {

    /**
     * @param ModelPerson $person
     * @return ValidationLog
     */
    public function run(ModelPerson $person): ValidationLog {
        $contestantYears = [1 => [], 2 => []];
        $participantsYears = [1 => [], 2 => []];
        foreach ($person->getEventParticipant() as $row) {
            $eventParticipant = ModelEventParticipant::createFromActiveRow($row);
            $year = $eventParticipant->getEvent()->year;
            $contestId = $eventParticipant->getEvent()->getContest()->contest_id;
            if (!\in_array($year, $participantsYears[$contestId])) {
                $participantsYears[$contestId][] = $year;
            }
        }
        foreach ($person->getContestants() as $row) {
            $contestant = ModelContestant::createFromActiveRow($row);
            $year = $contestant->year;
            $contestId = $contestant->contest_id;
            if (!\in_array($year, $contestantYears[$contestId])) {
                $contestantYears[$contestId][] = $year;
            }
        }
        $eventOrgYears = $this->getEventOrgYears($person);

        $log = '';
        $log .= $this->check($participantsYears, $eventOrgYears, 'eventParticipant', $person);
        $log .= $this->check($contestantYears, $eventOrgYears, 'contestant', $person);

        if ($log) {
            return new ValidationLog($this->getTitle(), $log, ValidationLog::LVL_DANGER);
        }
        return new ValidationLog($this->getTitle(), 'Test pass', ValidationLog::LVL_SUCCESS);
    }

    /**
     * @param int[][] $data
     * @param array $orgs
     * @param string $type
     * @param ModelPerson $person
     * @return string
     */
    private function check(array $data, array $orgs, string $type, ModelPerson $person): string {
        $log = '';
        foreach ($data as $contestId => $contestYears) {
            foreach ($contestYears as $year) {
                if (\in_array($year, $orgs[$contestId])) {
                    $log .= $this->createLog($year, $contestId, $type, 'eventOrg');
                }
                $query = $person->getOrgs($contestId);
                foreach ($query as $row) {
                    $org = ModelOrg::createFromActiveRow($row);
                    if ($org->until) {
                        if ($org->until >= $year && $org->since <= $year) {
                            $log .= $this->createLog($year, $contestId, $type, 'org');
                        }
                    } else {
                        if ($org->since <= $year) {
                            $log .= $this->createLog($year, $contestId, $type, 'org');
                        }
                    }
                }
            }
        }
        return $log;
    }

    /**
     * @param int $year
     * @param int $contestId
     * @param string $typeP
     * @param string $typeO
     * @return string
     */
    private function createLog(int $year, int $contestId, string $typeP, string $typeO): string {
        return \sprintf(_('Organization and participation at same year %d and contestId %d %s<->%s. '), $year, $contestId, $typeP, $typeO) . "\n";
    }

    /**
     * @param ModelPerson $person
     * @return array
     */
    private function getEventOrgYears(ModelPerson $person): array {
        $eventOrgYears = [1 => [], 2 => []];
        foreach ($person->getEventOrg() as $row) {
            $eventOrg = ModelEventOrg::createFromActiveRow($row);
            $year = $eventOrg->getEvent()->year;
            $contestId = $eventOrg->getEvent()->getContest()->contest_id;
            if (!\in_array($year, $eventOrgYears[$contestId])) {
                $eventOrgYears[$contestId][] = $year;
            }
        }
        return $eventOrgYears;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Organization and participation at same year');
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return 'organization_participation_same_year';
    }
}
