<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Timeline;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\YearCalculator;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class TimelineComponent extends FrontEndComponent
{

    private ModelPerson $person;

    public function __construct(Container $container, ModelPerson $person)
    {
        parent::__construct($container, 'chart.person.detail.timeline');
        $this->person = $person;
    }

    private function eventToArray(ModelEvent $event): array
    {
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
    private function calculateData(): array
    {
        $dates = [
            'since' => [],
            'until' => [],
        ];
        $organisers = [];
        foreach ($this->person->getOrgs() as $row) {
            $org = ModelOrg::createFromActiveRow($row);
            $since = new \DateTime(
                $org->getContest()->getContestYear($org->since)->ac_year . '-' . YearCalculator::FIRST_AC_MONTH . '-1'
            );
            $until = new \DateTime();
            if ($org->until) {
                $until = new \DateTime(
                    $org->getContest()->getContestYear(
                        $org->until
                    )->ac_year . '-' . YearCalculator::FIRST_AC_MONTH . '-1'
                );
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
            $year = $contestant->getContest()->getContestYear($contestant->year)->ac_year;

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

    private function calculateEvents(): array
    {
        $events = [];
        $eventParticipants = [];
        foreach ($this->person->getEventParticipants() as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);
            $events[] = $participant->getEvent();
            $eventParticipants[] = ['event' => $this->eventToArray($participant->getEvent()), 'model' => null];
        }
        $eventOrganisers = [];
        foreach ($this->person->getEventOrgs() as $row) {
            $eventOrg = ModelEventOrg::createFromActiveRow($row);
            $events[] = $eventOrg->getEvent();
            $eventOrganisers[] = ['event' => $this->eventToArray($eventOrg->getEvent()), 'model' => null];
        }
        $eventTeachers = [];
        foreach ($this->person->getEventTeachers() as $row) {
            $team = TeamModel::createFromActiveRow($row);
            $eventTeachers[] = ['event' => $this->eventToArray($team->getEvent()), 'model' => null];
            $events[] = $team->getEvent();
        }
        return [$events, [
            'eventOrgs' => $eventOrganisers,
            'eventParticipants' => $eventParticipants,
            'eventTeachers' => $eventTeachers,
        ]];
    }

    /**
     * @param ModelEvent[] $events
     * @return \DateTimeInterface[]
     */
    private function calculateFirstAndLast(array $events, array $dates): array
    {
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
     * @throws \Exception
     */
    public function getData(): array
    {
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
