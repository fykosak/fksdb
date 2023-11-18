<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Components\EntityForms\Fyziklani\FOFCategoryProcessing;
use FKSDB\Components\EntityForms\Fyziklani\NoMemberException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * @phpstan-type TeamStats array{
 *      data:array<int,array{
 *          task_id:int,
 *          points:int|null,
 *          time:\DateTimeInterface,
 *      }>,
 *      sum:int,
 *      count:int,
 *      pointsCount:array<int,int>,
 * }
 */
class RankingStrategy
{
    use SmartObject;

    private TeamService2 $teamService;
    private EventModel $event;

    public function __construct(EventModel $event, TeamService2 $teamService)
    {
        $this->teamService = $teamService;
        $this->event = $event;
    }

    /**
     * @throws NotClosedTeamException
     */
    public function __invoke(?TeamCategory $category = null): Html
    {
        $connection = $this->teamService->explorer->getConnection();
        try {
            $connection->beginTransaction();
            $teams = $this->getAllTeams($category);
            $teamsData = $this->getTeamsStats($teams);
            usort($teamsData, self::getSortFunction());
            $log = $this->saveResults($teamsData, is_null($category));
            $connection->commit();
        } catch (NoMemberException $exception) {
            $connection->rollBack();
            throw $exception;
        }

        return $log;
    }

    /**
     * @phpstan-param array<int,array{team:TeamModel2}> $data
     */
    private function saveResults(array $data, bool $total): Html
    {
        $log = Html::el('ul');
        foreach ($data as $index => $teamData) {
            $rank = $index + 1;
            $team = $teamData['team'];
            if ($total) {
                $this->teamService->storeModel(['rank_total' => $rank], $team);
            } else {
                $this->teamService->storeModel(['rank_category' => $rank], $team);
            }
            $log->addHtml(
                Html::el('li')
                    ->addText(sprintf(_('Team %s (%d) - rank: %d'), $team->name, $team->fyziklani_team_id, $rank))
            );
        }
        return $log;
    }

    /**
     * @throws NoMemberException
     */
    private static function getSortFunction(): callable
    {
        return function (array $b, array $a): int {

            // sort by points
            $diffPoints = $a['points'] - $b['points'];
            if ($diffPoints !== 0) {
                return $diffPoints;
            }

            // sort by average points
            if ($a['submits']['count'] && $b['submits']['count']) {
                // points must be equal in this step, so just compare the submit counts instead of averages
                $diffCount = $b['submits']['count'] - $a['submits']['count'];
                if ($diffCount !== 0) {
                    return $diffCount;
                }
            }

            // sort by number of submits with given points
            $diffCountFive = $a['submits']['pointsCount'][5] - $b['submits']['pointsCount'][5];
            if ($diffCountFive !== 0) {
                return $diffCountFive;
            }

            $diffCountThree = $a['submits']['pointsCount'][3] - $b['submits']['pointsCount'][3];
            if ($diffCountThree !== 0) {
                return $diffCountThree;
            }

            // coefficients
            $aCoef = FOFCategoryProcessing::getCoefficientAvg($a['team']->getPersons(), $a['team']->event);
            $bCoef = FOFCategoryProcessing::getCoefficientAvg($b['team']->getPersons(), $b['team']->event);

            if ($aCoef < $bCoef) {
                return 1;
            } elseif ($aCoef > $bCoef) {
                return -1;
            }

            // team id
            return $b['team']->fyziklani_team_id <=> $a['team']->fyziklani_team_id;
        };
    }

    /**
     * @phpstan-return TeamModel2[]
     */
    public function getInvalidTeamsPoints(?TeamCategory $category = null): array
    {
        $invalidTeams = [];
        $teams = $this->getAllTeams($category);
        /** @var TeamModel2 $team */
        foreach ($teams as $team) {
            $sum = (int)$team->getNonRevokedSubmits()->sum('points');
            if ($team->points !== $sum) {
                $invalidTeams[] = $team;
            }
        }

        return $invalidTeams;
    }

    /**
     * Validate ranking of teams
     * @phpstan-return TeamModel2[]
     * @throws NoMemberException
     */
    public function getInvalidTeamsRank(?TeamCategory $category = null): array
    {
        $compareFunction = self::getSortFunction();

        $teams = $this->getAllTeams($category);
        $teamsData = $this->getTeamsStats($teams);

        $invalidTeams = [];

        usort($teamsData, function ($a, $b) use ($category) {
            if (is_null($category)) {
                return $a['team']->rank_total - $b['team']->rank_total;
            }
            return $a['team']->rank_category - $b['team']->rank_category;
        });

        for ($i = 0; $i < count($teamsData) - 1; $i++) {
            $a = $teamsData[$i];
            $b = $teamsData[$i + 1];
            $currentRank = is_null($category) ? $a['team']->rank_total : $a['team']->rank_category;
            $nextRank = is_null($category) ? $b['team']->rank_total : $b['team']->rank_category;

            // if the ranking is continuous
            if ($currentRank + 1 !== $nextRank) {
                $invalidTeams[] = $a['team'];
                continue;
            }

            if ($compareFunction($b, $a) < 1) {
                $invalidTeams[] = $a['team'];
            }
        }

        return $invalidTeams;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    private function getAllTeams(?TeamCategory $category = null): TypedGroupedSelection
    {
        $query = $this->event->getParticipatingTeams();
        if ($category) {
            $query->where('category', $category->value);
        }
        return $query;
    }

    /**
     * @phpstan-return array<int,array{
     *     points:int|null,
     *     submits:TeamStats,
     *     team:TeamModel2,
     * }>
     * @throws NotClosedTeamException
     * @phpstan-param TypedGroupedSelection<TeamModel2> $teams
     */
    private function getTeamsStats(TypedGroupedSelection $teams): array
    {
        $teamsData = [];
        /** @var TeamModel2 $team */
        foreach ($teams as $team) {
            if ($team->hasOpenSubmitting()) {
                throw new NotClosedTeamException($team);
            }
            $teamData = [
                'points' => $team->points,
                'submits' => $this->getAllSubmits($team),
                'team' => $team,
            ];

            $teamsData[] = $teamData;
        }
        return $teamsData;
    }

    /**
     * @phpstan-return TeamStats
     */
    protected function getAllSubmits(TeamModel2 $team): array
    {
        $arraySubmits = [];
        $sum = 0;
        $count = 0;

        $availablePoints = $this->event->getGameSetup()->getAvailablePoints();
        $submitPointsCount = [];
        foreach ($availablePoints as $points) {
            $submitPointsCount[$points] = 0;
        }

        /** @var SubmitModel $submit */
        foreach ($team->getSubmits() as $submit) {
            if ($submit->points === null) {
                continue;
            }

            $submitPointsCount[$submit->points]++;
            $sum += $submit->points;
            $count++;
            $arraySubmits[] = [
                'task_id' => $submit->fyziklani_task_id,
                'points' => $submit->points,
                'time' => $submit->modified,
            ];
        }

        return [
            'data' => $arraySubmits,
            'sum' => $sum,
            'count' => $count,
            'pointsCount' => $submitPointsCount,
        ];
    }
}
