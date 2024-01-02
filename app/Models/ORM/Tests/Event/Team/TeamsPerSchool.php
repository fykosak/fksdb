<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event\Team;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<TeamModel2>
 */
class TeamsPerSchool extends Test
{
    private const TEAM_LIMIT = 4;

    /**
     * @param TeamModel2 $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        foreach ($this->getSchoolsFromTeam($model) as $school) {
            $query = $model->event->getTeams()
                ->where(
                    ':fyziklani_team_member.person:person_history.ac_year',
                    $model->event->getContestYear()->ac_year
                )
                ->where(':fyziklani_team_member.person:person_history.school_id', $school->school_id)
                ->order('fyziklani_team.fyziklani_team_id');
            $teamCount = 0;
            /** @var TeamModel2 $team */
            foreach ($query as $team) {
                $teamCount++;
                if ($team->fyziklani_team_id === $model->fyziklani_team_id && $teamCount > self::TEAM_LIMIT) {
                    $logger->log(
                        new TestMessage($id, sprintf(_('%dth team per school'), $teamCount), Message::LVL_ERROR)
                    );
                }
            }
            if ($teamCount > self::TEAM_LIMIT) {
                $logger->log(
                    new TestMessage(
                        $id,
                        sprintf(_('School %s has total %d teams'), $school->label()->toText(), $teamCount),
                        Message::LVL_WARNING
                    )
                );
            }
        }
    }

    /**
     * @phpstan-return SchoolModel[]
     */
    public static function getSchoolsFromTeam(TeamModel2 $team): array
    {
        $schools = [];
        /** @var TeamMemberModel $member */
        foreach ($team->getMembers() as $member) {
            $history = $member->getPersonHistory();
            $schools[$history->school_id] = $history->school;
        }
        return $schools;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    public static function getTeamsFromSchool(SchoolModel $school, EventModel $event): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TeamModel2> $query */
        $query = $event->getTeams()
            ->where(
                ':fyziklani_team_member.person:person_history.ac_year',
                $event->getContestYear()->ac_year
            )
            ->where(':fyziklani_team_member.person:person_history.school_id', $school->school_id)
            ->order('fyziklani_team.fyziklani_team_id');
        return $query;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Teams per school'));
    }

    public function getId(): string
    {
        return 'teamsPerSchool';
    }
}
