<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Event;

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
class TeamListWebModel extends WebModel
{
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    /**
     * @throws BadRequestException
     */
    public function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        /** @var TeamModel2 $team */
        foreach ($event->getTeams() as $team) {
            $teamData = $team->__toArray();
            $teamData['teachers'] = [];
            $teamData['members'] = [];
            /** @var TeamTeacherModel $teacher */
            foreach ($team->getTeachers() as $teacher) {
                $teamData['teachers'][] = $teacher->person->__toArray();
            }
            /** @var TeamMemberModel $member */
            foreach ($team->getMembers() as $member) {
                $history = $member->getPersonHistory();
                $school = $history->school;
                $teamData['members'][] = array_merge($member->person->__toArray(), [
                    'school' => $school ? $school->__toArray() : null,
                    'studyYear' => $history ? $history->study_year_new->value : null,
                ]);
            }
            $data[] = $teamData;
        }
        return $data;
    }
}
