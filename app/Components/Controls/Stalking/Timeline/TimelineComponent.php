<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Timeline;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrgModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

/**
 * @phpstan-import-type SerializedEventModel from EventModel
 * @phpstan-type EventContribution array{
 *     eventOrgs:array<int,array{event:SerializedEventModel,model:null}>,
 *     eventParticipants:array<int,array{event:SerializedEventModel,model:null}>,
 *     eventTeachers:array<int,array{event:SerializedEventModel,model:null}>,
 * }
 * @phpstan-type StateContribution array{
 *     orgs:array<int,array{
 *          since:string,
 *          until:string,
 *          model:array{orgId:int,contestId:int}
 * }>,
 *     contestants:array<int,array{
 *          since:string,
 *          until:string,
 *          model:array{contestantId:int,contestId:int}
 * }>,
 * }
 */
class TimelineComponent extends FrontEndComponent
{
    private PersonModel $person;

    public function __construct(Container $container, PersonModel $person)
    {
        parent::__construct($container, 'chart.person.detail.timeline');
        $this->person = $person;
    }

    /**
     * @throws \Exception
     * @phpstan-return array{
     *  array{
     *      since:\DateTimeInterface[],
     *      until:\DateTimeInterface[],
     *  },
     *  StateContribution,
     * }
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

    /**
     * @phpstan-return array{EventModel[],EventContribution}
     */
    private function calculateEvents(): array
    {
        $events = [];
        $eventParticipants = [];
        /** @var EventParticipantModel $participant */
        foreach ($this->person->getEventParticipants() as $participant) {
            $events[] = $participant->event;
            $eventParticipants[] = ['event' => $participant->event->__toArray(), 'model' => null];
        }
        $eventOrganisers = [];
        /** @var EventOrgModel $eventOrg */
        foreach ($this->person->getEventOrgs() as $eventOrg) {
            $events[] = $eventOrg->event;
            $eventOrganisers[] = ['event' => $eventOrg->event->__toArray(), 'model' => null];
        }
        $eventTeachers = [];
        /** @var TeamTeacherModel $teacher */
        foreach ($this->person->getFyziklaniTeachers() as $teacher) {
            $eventTeachers[] = [
                'event' => $teacher->fyziklani_team->event->__toArray(),
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
     * @phpstan-return \DateTimeInterface[]
     * @phpstan-param array<string,\DateTimeInterface[]> $dates
     */
    private function calculateFirstAndLast(array $events, array $dates): array
    {
        $first = $this->person->created;
        $last = new \DateTime();
        foreach ($events as $event) {
            if ($event->begin < $first) {
                $first = $event->begin;
            }
            if ($event->end > $last) {
                $last = $event->end;
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
     * @phpstan-return array{
     *     scale:array{max:string,min:string},
     *     events:EventContribution,
     *     states:StateContribution,
     * }
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
