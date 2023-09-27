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
 * @phpstan-extends WebModel<array{eventId:int},array>
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
        if (is_null($event)) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        /** @var TeamModel2 $team */
        foreach ($event->getTeams() as $team) {
            $teamData = [
                'teamId' => $team->fyziklani_team_id,
                'name' => $team->name,
                'status' => $team->state->value,
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
                $teamData['teachers'][] = [
                    'name' => $teacher->person->getFullName(),
                    'personId' => $teacher->person->person_id,
                    'email' => $teacher->person->getInfo()->email,
                ];
            }
            /** @var TeamMemberModel $member */
            foreach ($team->getMembers() as $member) {
                $history = $member->getPersonHistory();
                $teamData['members'][] = [
                    'name' => $member->person->getFullName(),
                    'personId' => $member->person->person_id,
                    'email' => $member->person->getInfo()->email,
                    'schoolId' => $history ? $history->school_id : null,
                    'schoolName' => $history ? $history->school->name_abbrev : null,
                    'studyYear' => $history ? $history->study_year_new->numeric() : null,
                    'studyYearNew' => $history ? $history->study_year_new->value : null,
                    'countryIso' => $history ? (
                    ($school = $history->school) ? $school->address->country->alpha_2 : null
                    ) : null,
                ];
            }
            $data[] = $teamData;
        }
        return $data;
    }
}
