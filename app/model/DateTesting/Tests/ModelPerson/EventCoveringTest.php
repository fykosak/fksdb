<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\DataTesting\TestLog;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\TestsLogger;

/**
 * Class EventCoveringTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventCoveringTest extends PersonTest {

    public function run(TestsLogger $logger, ModelPerson $person): void {
        $contestantYears = [
            ModelContest::ID_FYKOS => [],
            ModelContest::ID_VYFUK => [],
        ];
        $participantsYears = [
            ModelContest::ID_FYKOS => [],
            ModelContest::ID_VYFUK => [],
        ];
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

        $this->check($logger, $participantsYears, $eventOrgYears, 'eventParticipant', $person);
        $this->check($logger, $contestantYears, $eventOrgYears, 'contestant', $person);
    }

    /**
     * @param TestsLogger $logger
     * @param int[][] $data
     * @param array $orgs
     * @param string $type
     * @param ModelPerson $person
     */
    private function check(TestsLogger $logger, array $data, array $orgs, string $type, ModelPerson $person): void {
        foreach ($data as $contestId => $contestYears) {
            foreach ($contestYears as $year) {
                if (\in_array($year, $orgs[$contestId])) {
                    $logger->log($this->createLog($year, $contestId, $type, 'eventOrg'));
                }
                $query = $person->getOrgs($contestId);
                foreach ($query as $row) {
                    $org = ModelOrg::createFromActiveRow($row);
                    if ($org->until) {
                        if ($org->until >= $year && $org->since <= $year) {
                            $logger->log($this->createLog($year, $contestId, $type, 'org'));
                        }
                    } elseif ($org->since <= $year) {
                        $logger->log($this->createLog($year, $contestId, $type, 'org'));
                    }
                }
            }
        }
    }

    private function createLog(int $year, int $contestId, string $typeP, string $typeO): TestLog {
        return new TestLog($this->getTitle(), \sprintf(_('Organization and participation at same year %d and contestId %d %s<->%s. '), $year, $contestId, $typeP, $typeO), TestLog::LVL_DANGER);
    }

    private function getEventOrgYears(ModelPerson $person): array {
        $eventOrgYears = [
            ModelContest::ID_FYKOS => [],
            ModelContest::ID_VYFUK => [],
        ];
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

    public function getTitle(): string {
        return _('Organization and participation at same year');
    }

    public function getAction(): string {
        return 'organization_participation_same_year';
    }
}
