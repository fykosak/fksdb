<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\DataTesting\TestLog;
use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\EventOrgModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\Logging\Message;

class EventCoveringTest extends PersonTest
{

    public function __construct()
    {
        parent::__construct('organization_participation_same_year', _('Organization and participation at same year'));
    }

    public function run(Logger $logger, PersonModel $person): void
    {
        $contestantYears = [
            ContestModel::ID_FYKOS => [],
            ContestModel::ID_VYFUK => [],
        ];
        $participantsYears = [
            ContestModel::ID_FYKOS => [],
            ContestModel::ID_VYFUK => [],
        ];
        /** @var EventParticipantModel $eventParticipant */
        foreach ($person->getEventParticipants() as $eventParticipant) {
            $year = $eventParticipant->event->year;
            $contestId = $eventParticipant->event->event_type->contest_id;
            if (!\in_array($year, $participantsYears[$contestId])) {
                $participantsYears[$contestId][] = $year;
            }
        }
        /** @var ContestantModel $contestant */
        foreach ($person->getContestants() as $contestant) {
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

    private function check(Logger $logger, array $data, array $organisers, string $type, PersonModel $person): void
    {
        foreach ($data as $contestId => $contestYears) {
            foreach ($contestYears as $year) {
                if (\in_array($year, $organisers[$contestId])) {
                    $logger->log($this->createLog($year, $contestId, $type, 'eventOrg'));
                }
                $query = $person->getOrgs($contestId);
                /** @var OrgModel $org */
                foreach ($query as $org) {
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

    private function createLog(int $year, int $contestId, string $typeP, string $typeO): TestLog
    {
        return new TestLog(
            $this->title,
            \sprintf(
                _('Organization and participation at same year %d and contestId %d %s<->%s.'),
                $year,
                $contestId,
                $typeP,
                $typeO
            ),
            Message::LVL_ERROR
        );
    }

    private function getEventOrgYears(PersonModel $person): array
    {
        $eventOrgYears = [
            ContestModel::ID_FYKOS => [],
            ContestModel::ID_VYFUK => [],
        ];
        /** @var EventOrgModel $eventOrg */
        foreach ($person->getEventOrgs() as $eventOrg) {
            $year = $eventOrg->event->year;
            $contestId = $eventOrg->event->event_type->contest_id;
            if (!\in_array($year, $eventOrgYears[$contestId])) {
                $eventOrgYears[$contestId][] = $year;
            }
        }
        return $eventOrgYears;
    }
}
