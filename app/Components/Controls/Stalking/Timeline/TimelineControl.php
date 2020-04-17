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
 * @package FKSDB\Components\Controls\Stalking\Timeline
 */
class TimelineControl extends ReactComponent {
    /**
     * @var YearCalculator
     */
    private $yearCalculator;
    /**
     * @var ModelPerson
     */
    private $person;

    /**
     * TimelineControl constructor.
     * @param Container $container
     * @param ModelPerson $person
     */
    public function __construct(Container $container, ModelPerson $person) {
        parent::__construct($container);
        $this->person = $person;
        $this->yearCalculator = $container->getByType(YearCalculator::class);
    }

    /**
     * @param ModelEvent $event
     * @return array
     */
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
     * @return array
     * @throws \Exception
     */
    private function calculateData() {

        $dates = [
            'since' => [],
            'until' => []
        ];
        $orgs = [];
        foreach ($this->person->getOrgs() as $row) {
            $org = ModelOrg::createFromActiveRow($row);
            $since = new \DateTime($this->yearCalculator->getAcademicYear($org->getContest(), $org->since) . '-' . YearCalculator::FIRST_AC_MONTH . '-1');
            $until = new \DateTime();
            if ($org->until) {
                $until = new \DateTime($this->yearCalculator->getAcademicYear($org->getContest(), $org->until) . '-' . YearCalculator::FIRST_AC_MONTH . '-1');
            }
            $dates['since'][] = $since;
            $dates['until'][] = $until;
            $orgs[] = ['since' => $since->format('c'), 'until' => $until->format('c'), 'model' => [
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
            'orgs' => $orgs,
            'contestants' => $contestants,
        ]];
    }

    /**
     * @return array
     */
    private function calculateEvents(): array {
        $events = [];
        $eventParticipants = [];
        foreach ($this->person->getEventParticipant() as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);
            $events[] = $participant->getEvent();
            $eventParticipants[] = ['event' => $this->eventToArray($participant->getEvent()), 'model' => null];
        }
        $eventOrgs = [];
        foreach ($this->person->getEventOrg() as $row) {
            $eventOrg = ModelEventOrg::createFromActiveRow($row);
            $events[] = $eventOrg->getEvent();
            $eventOrgs[] = ['event' => $this->eventToArray($eventOrg->getEvent()), 'model' => null];
        }
        $eventTeachers = [];
        foreach ($this->person->getEventTeacher() as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $eventTeachers = ['event' => $this->eventToArray($team->getEvent()), 'model' => null];
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
     * @param \DateTime[][] $dates
     * @return \DateTime[]
     * @throws \Exception
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
     * @inheritDoc
     */
    protected function getReactId(): string {
        return 'person.detail.timeline';
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    function getData(): string {
        list($events, $calculatedEvents) = $this->calculateEvents();
        list($dates, $longTimeEvents) = $this->calculateData();
        list($first, $last) = $this->calculateFirstAndLast($events, $dates);

        $data = [
            'scale' => [
                'max' => $last->format('c'),
                'min' => $first->format('c'),
            ],
            'events' => $calculatedEvents,
            'states' => $longTimeEvents,
        ];
        return json_encode($data);
    }
}
