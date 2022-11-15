<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail\Timeline;

use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrgModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class TimelineComponent extends FrontEndComponent
{

    private PersonModel $person;

    public function __construct(Container $container, PersonModel $person)
    {
        parent::__construct($container, 'chart.person.detail.timeline');
        $this->person = $person;
    }

    private function eventToArray(EventModel $event): array
    {
        return [
            'eventId' => $event->event_id,
            'name' => $event->name,
            'contestId' => $event->event_type->contest_id,
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
        /** @var OrgModel $org */
        foreach ($this->person->getOrganisers() as $org) {
            $since = new \DateTime(
                $org->contest->getContestYear($org->since)->ac_year . '-' . ContestYearService::FIRST_AC_MONTH . '-1'
            );
            $until = new \DateTime();
            if ($org->until) {
                $until = new \DateTime(
                    $org->contest->getContestYear(
                        $org->until
                    )->ac_year . '-' . ContestYearService::FIRST_AC_MONTH . '-1'
                );
            }
            $dates['since'][] = $since;
            $dates['until'][] = $until;
            $organisers[] = [
                'since' => $since->format('c'),
                'until' => $until->format('c'),
                'model' => [
                    'orgId' => $org->org_id,
                    'contestId' => $org->contest_id,
                ],
            ];
        }
        $contestants = [];
        /** @var ContestantModel $contestant */
        foreach ($this->person->getContestants() as $contestant) {
            $year = $contestant->contest->getContestYear($contestant->year)->ac_year;

            $since = new \DateTime($year . '-' . ContestYearService::FIRST_AC_MONTH . '-1');
            $until = new \DateTime(($year + 1) . '-' . ContestYearService::FIRST_AC_MONTH . '-1');
            $dates['since'][] = $since;
            $dates['until'][] = $until;
            $contestants[] = [
                'since' => $since->format('c'),
                'until' => $until->format('c'),
                'model' => [
                    'contestantId' => $contestant->contestant_id,
                    'contestId' => $contestant->contest_id,
                ],
            ];
        }
        return [
            $dates,
            [
                'orgs' => $organisers,
                'contestants' => $contestants,
            ],
        ];
    }

    private function calculateEvents(): array
    {
        $events = [];
        $eventParticipants = [];
        /** @var EventParticipantModel $participant */
        foreach ($this->person->getEventParticipants() as $participant) {
            $events[] = $participant->event;
            $eventParticipants[] = ['event' => $this->eventToArray($participant->event), 'model' => null];
        }
        $eventOrganisers = [];
        /** @var EventOrgModel $eventOrg */
        foreach ($this->person->getEventOrgs() as $eventOrg) {
            $events[] = $eventOrg->event;
            $eventOrganisers[] = ['event' => $this->eventToArray($eventOrg->event), 'model' => null];
        }
        $eventTeachers = [];
        /** @var TeamTeacherModel $teacher */
        foreach ($this->person->getFyziklaniTeachers() as $teacher) {
            $eventTeachers[] = [
                'event' => $this->eventToArray($teacher->fyziklani_team->event),
                'model' => null,
            ];
            $events[] = $teacher->fyziklani_team->event;
        }
        return [
            $events,
            [
                'eventOrgs' => $eventOrganisers,
                'eventParticipants' => $eventParticipants,
                'eventTeachers' => $eventTeachers,
            ],
        ];
    }

    /**
     * @param EventModel[] $events
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
