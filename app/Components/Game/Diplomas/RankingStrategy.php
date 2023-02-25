<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Components\EntityForms\Fyziklani\FOFCategoryProcessing;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\SmartObject;
use Nette\Utils\Html;

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
    public function close(?TeamCategory $category = null): Html
    {
        $connection = $this->teamService->explorer->getConnection();
        $connection->beginTransaction();
        $teams = $this->getAllTeams($category);
        $teamsData = $this->getTeamsStats($teams);
        usort($teamsData, self::getSortFunction());
        $log = $this->saveResults($teamsData, is_null($category));
        $connection->commit();
        return $log;
    }

    private function saveResults(array $data, bool $total): Html
    {
        $log = Html::el('ul');
        foreach ($data as $index => $teamData) {
            $rank = $index + 1;
            /** @var TeamModel2 $team */
            $team = $teamData['team'];
            if ($total) {
                $this->teamService->storeModel(['rank_total' => $rank], $team);
            } else {
                $this->teamService->storeModel(['rank_category' => $rank], $team);
            }
            $log->addHtml(
                Html::el('li')
                    ->addText(
                        _('Team') . " " . $team->name . ' (' . $team->fyziklani_team_id . ')'
                        . " - " . _('Rank') . ': ' . ($rank)
                    )
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
            $d = $a['points'] - $b['points'];
            if ($d !== 0) {
                return $d;
            }

            // sort by average points
            if ($a['submits']['count'] && $b['submits']['count']) {
                // points must be equal in this step, so just compare the submit counts instead of averages
                $d = $b['submits']['count'] - $a['submits']['count'];
                if ($d !== 0) {
                    return $d;
                }
            }

            // sort by number of submits with given points
            $d = $a['submits']['pointsCount'][5] - $b['submits']['pointsCount'][5];
            if ($d !== 0) {
                return $d;
            }

            $d = $a['submits']['pointsCount'][3] - $b['submits']['pointsCount'][3];
            if ($d !== 0) {
                return $d;
            }

            // coefficients
            $ac = FOFCategoryProcessing::getCoefficientAvg($a['team']->getPersons(), $a['team']->event);
            $bc = FOFCategoryProcessing::getCoefficientAvg($b['team']->getPersons(), $b['team']->event);

            if ($ac < $bc) {
                return 1;
            } elseif ($ac > $bc) {
                return -1;
            }

            // team creation date
            if ($a['team']->created < $b['team']->created) {
                return 1;
            } elseif ($a['team']->created > $b['team']->created) {
                return -1;
            }

            // in case everything fails (at least team ids should be different)
            return 0;
        };
    }

    public function getInvalidTeamsPoints(?TeamCategory $category = null): array
    {
        $invalidTeams = [];
        $teams = $this->getAllTeams($category);
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
     * @param array $first expects teamData array from getTeamsStats
     */
    public function getInvalidTeamsRank(?TeamCategory $category = null): array
    {
        $compareFunction = self::getSortFunction();

        $teams = $this->getAllTeams($category);
        $teamsData = $this->getTeamsStats($teams);

        $invalidTeams = [];

        usort($teamsData, function ($a, $b) {
            if (!is_null($category)) {
                return $a['team']->rank_category - $b['team']->rank_category;
            }
            return $a['team']->rank_total - $b['team']->rank_total;
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


    private function getAllTeams(?TeamCategory $category = null): TypedGroupedSelection
    {
        $query = $this->event->getParticipatingTeams();
        if ($category) {
            $query->where('category', $category->value);
        }
        return $query;
    }

    /**
     * @return array[]
     * @throws NotClosedTeamException
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
     * @return array[]|int[]
     */
    protected function getAllSubmits(TeamModel2 $team): array
    {
        $arraySubmits = [];
        $sum = 0;
        $count = 0;

        $availablePoints = $this->event->getGameSetup()->available_points ?? '';
        $maxPoints = max(array_map('intval', explode(',', $availablePoints)));
        $submitPointsCount = array_fill(0, $maxPoints + 1, 0);

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
            'pointsCount' => $submitPointsCount
        ];
    }
}
