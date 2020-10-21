<?php

namespace FKSDB\Components\Controls\Stalking\Timeline;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\YearCalculator;
use Nette\DI\Container;

/**
 * Class TimelineControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TimelineControl extends ReactComponent {

    private ModelPerson $person;

    private YearCalculator $yearCalculator;

    public function __construct(Container $container, ModelPerson $person) {
        parent::__construct($container, 'person.detail.timeline');
        $this->person = $person;
    }

    final public function injectYearCalculator(YearCalculator $yearCalculator): void {
        $this->yearCalculator = $yearCalculator;
    }

    private function eventToArray(ModelEvent $event): array {
        return [
            'eventId' => $event->event_id,
            'name' => $event->name,
            'contestId' => $event->getContest()->contest_id,
            'begin' => $event->begin->format('c'),
            'eventTypeId' => $event->event_type_id,
        ];
    }

    /**
     * @return \array[][]
     * @throws \Exception
     */
    private function calculateData(): array {

        $dates = [
            'since' => [],
            'until' => [],
        ];
        $organisers = [];
        foreach ($this->person->getOrgs() as $row) {
            $org = ModelOrg::createFromActiveRow($row);
            $since = new \DateTime($this->yearCalculator->getAcademicYear($org->getContest(), $org->since) . '-' . YearCalculator::FIRST_AC_MONTH . '-1');
            $until = new \DateTime();
            if ($org->until) {
                $until = new \DateTime($this->yearCalculator->getAcademicYear($org->getContest(), $org->until) . '-' . YearCalculator::FIRST_AC_MONTH . '-1');
            }
            $dates['since'][] = $since;
            $dates['until'][] = $until;
            $organisers[] = ['since' => $since->format('c'), 'until' => $until->format('c'), 'model' => [
                'orgId' => $org->org_id,
                'contestId' => $org->contest_id,
            ]];
        }
        $contestants = [];
        foreach ($this->person->getContestants() as $row) {
            $contestant = ModelContestant::createFromActiveRow($row);
            $year = $this->yearCalculator->getAcademicYear($contestant->getContest(), $contestant->year);

            $since = new \DateTime($year . '-' . YearCalculator::FIRST_AC_MONTH . '-1');
            $until = new \DateTime(($year + 1) . '-' . YearCalculator::FIRST_AC_MONTH . '-1');
            $dates['since'][] = $since;
            $dates['until'][] = $until;
            $contestants[] = [
                'since' => $since->format('c'),
                'until' => $until->format('c'),
                'model' => [
                    'contestantId' => $contestant->ct_id,
                    'contestId' => $contestant->contest_id,
                ]];
        }
        return [$dates, [
            'orgs' => $organisers,
            'contestants' => $contestants,
        ]];
    }

    private function calculateEvents(): array {
        $events = [];
        $eventParticipants = [];
        foreach ($this->person->getEventParticipants() as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);
            $events[] = $participant->getEvent();
            $eventParticipants[] = ['event' => $this->eventToArray($participant->getEvent()), 'model' => null];
        }
        $eventOrgs = [];
        foreach ($this->person->getEventOrgs() as $row) {
            $eventOrg = ModelEventOrg::createFromActiveRow($row);
            $events[] = $eventOrg->getEvent();
            $eventOrgs[] = ['event' => $this->eventToArray($eventOrg->getEvent()), 'model' => null];
        }
        $eventTeachers = [];
        foreach ($this->person->getEventTeachers() as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $eventTeachers[] = ['event' => $this->eventToArray($team->getEvent()), 'model' => null];
            $events[] = $team->getEvent();

        }
        return [$events, [
            'eventOrgs' => $eventOrgs,
            'eventParticipants' => $eventParticipants,
            'eventTeachers' => $eventTeachers,
        ]];
    }

    /**
     * @param ModelEvent[] $events
     * @param array $dates
     * @return \DateTimeInterface[]
     */
    private function calculateFirstAndLast(array $events, array $dates): array {
        $first = $this->person->created;
        $last = new \DateTime();
        foreach ($events as $event) {
            $begin = $event->begin;
            if ($begin < $first) {
                $first = $begin;
            }
            $end = $event->end;
            if ($end > $last) {
                $last = $end;
            }
        }
        foreach ($dates as $type => $dateTypes) {
            foreach ($dateTypes as $date) {
                switch ($type) {
                    case 'since':
                        if ($date < $first) {
                            $first = $date;
                        }
                        break;
                    case 'until':
                        if ($date > $last) {
                            $last = $date;
                        }
                        break;
                }
            }
        }
        return [$first, $last];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getData(): array {
        [$events, $calculatedEvents] = $this->calculateEvents();
        [$dates, $longTimeEvents] = $this->calculateData();
        [$first, $last] = $this->calculateFirstAndLast($events, $dates);

        return [
            'scale' => [
                'max' => $last->format('c'),
                'min' => $first->format('c'),
            ],
            'events' => $calculatedEvents,
            'states' => $longTimeEvents,
        ];
    }
}
