<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class EventWebModel extends WebModel
{

    private EventService $eventService;
    private PersonScheduleService $personScheduleService;

    public function inject(EventService $eventService, PersonScheduleService $personScheduleService): void
    {
        $this->eventService = $eventService;
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws GoneException
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        throw new GoneException();
    }

    private function createPersonScheduleArray(EventModel $event): array
    {
        $data = [];
        $query = $this->personScheduleService->getTable()
            ->where('schedule_item.schedule_group.event_id', $event->event_id);
        /** @var PersonScheduleModel $model */
        foreach ($query as $model) {
            $data[] = [
                'person' => [
                    'name' => $model->person->getFullName(),
                    'personId' => $model->person_id,
                    'email' => $model->person->getInfo()->email,
                ],
                'scheduleItemId' => $model->schedule_item_id,
            ];
        }
        return $data;
    }

    private function createScheduleListArray(EventModel $event): array
    {
        $data = [];
        /** @var ScheduleGroupModel $group */
        foreach ($event->getScheduleGroups() as $group) {
            $datum = $group->__toArray();
            $datum['schedule_items'] = [];
            /** @var ScheduleItemModel $item */
            foreach ($group->getItems() as $item) {
                $datum['schedule_items'][] = $item->__toArray();
            }
            $data[] = $datum;
        }
        return $data;
    }

    private function createTeamListArray(EventModel $event): array
    {
        $teamsData = [];
        /** @var TeamModel2 $team */
        foreach ($event->getFyziklaniTeams() as $team) {
            $teamData = [
                'teamId' => $team->fyziklani_team_id,
                'name' => $team->name,
                'status' => $team->state->value,
                'category' => $team->category->value,
                'created' => $team->created->format('c'),
                'phone' => $team->phone,
                'password' => $team->password,
                'points' => $team->points,
                'rankCategory' => $team->rank_category,
                'rankTotal' => $team->rank_total,
                'forceA' => $team->force_a,
                'gameLang' => $team->game_lang->value,
                'teachers' => [],
                'members' => [],
            ];
            /** @var TeamTeacherModel $teacher */
            foreach ($team->getTeachers() as $teacher) {
                $teamData['teachers'][] = [
                    'name' => $teacher->person->getFullName(),
                    'email' => $teacher->person->getInfo()->email,
                ];
            }
            /** @var TeamMemberModel $member */
            foreach ($team->getMembers() as $member) {
                $teamData['members'][] = $this->createParticipantArray($member);
            }
            $teamsData[$team->fyziklani_team_id] = $teamData;
        }
        return $teamsData;
    }

    private function createParticipantListArray(EventModel $event): array
    {
        $participants = [];
        /** @var EventParticipantModel $participant */
        foreach ($event->getParticipants() as $participant) {
            $participants[$participant->event_participant_id] = $this->createParticipantArray($participant);
        }
        return $participants;
    }

    /**
     * @param TeamMemberModel|EventParticipantModel $member
     */
    private function createParticipantArray($member): array
    {
        $history = $member->getPersonHistory();
        return [
            'name' => $member->person->getFullName(),
            'email' => $member->person->getInfo()->email,
            'schoolId' => $history ? $history->school_id : null,
            'schoolName' => $history ? $history->school->name_abbrev : null,
            'countryIso' => $history ? (
            ($school = $history->school)
                ? $school->address->region->country_iso
                : null
            ) : null,
        ];
    }

    /**
     * @throws BadRequestException
     * #Array
     */
    public function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['event_id']);
        if (is_null($event)) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = $event->__toArray();
        if ($event->isTeamEvent()) {
            $data['teams'] = $this->createTeamListArray($event);
        } else {
            $data['participants'] = $this->createParticipantListArray($event);
        }
        $data['schedule'] = $this->createScheduleListArray($event);
        $data['person_schedule'] = $this->createPersonScheduleArray($event);
        return $data;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'event_id' => Expect::scalar()->castTo('int')->required(),
        ]);
    }
}
