<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-import-type SerializedTeamModel from TeamModel2
 * @phpstan-extends WebModel<array{eventId:int},(SerializedTeamModel)[]>
 */
class TeamsWebModel extends WebModel
{
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    /**
     * @throws BadRequestException
     */
    protected function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        /** @var TeamModel2 $team */
        foreach ($event->getTeams() as $team) {
            $teamData = [
                'teamId' => $team->fyziklani_team_id,
                'name' => $team->name,
                'code' => $team->createMachineCode(),
                'status' => $team->state->value,
                'state' => $team->state->value,
                'category' => $team->category->value,
                'created' => $team->created->format('c'),
                'phone' => $team->phone,
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
                $teamData['teachers'][] = array_merge($teacher->person->__toArray(), [
                    'code' => $teacher->createMachineCode(),
                ]);
            }
            /** @var TeamMemberModel $member */
            foreach ($team->getMembers() as $member) {
                $history = $member->getPersonHistory();
                $school = $history->school;
                $teamData['members'][] = array_merge(
                    $member->person->__toArray(),
                    [
                        'code' => $member->createMachineCode(),
                        'school' => $school ? $school->__toArray() : null,
                        'studyYear' => $history ? $history->study_year_new->value : null,
                    ]
                );
            }
            $data[] = $teamData;
        }
        return $data;
    }

    protected function isAuthorized(array $params): bool
    {
        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            return false;
        }
        return $this->eventAuthorizator->isAllowed($event, 'api', $event);
    }
}
