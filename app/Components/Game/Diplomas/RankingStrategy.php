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

    private static function getSortFunction(): callable
    {
        return function (array $b, array $a): int {
            #sort by points
            if ($a['points'] > $b['points']) {
                return 1;
            } elseif ($a['points'] < $b['points']) {
                return -1;
            }

            # sort by average points
            if ($a['submits']['count'] && $b['submits']['count']) {
                $qa = $a['submits']['sum'] / $a['submits']['count'];
                $qb = $b['submits']['sum'] / $b['submits']['count'];
                if ($qa > $qb) {
                    return 1;
                } elseif ($qa < $qb) {
                    return -1;
                }
            }

            # sort by number of submits with given points
            if ($a['submits']['pointsCount'][5] > $b['submits']['pointsCount'][5]) {
                return 1;
            } elseif ($a['submits']['pointsCount'][5] < $b['submits']['pointsCount'][5]) {
                return -1;
            }

            if ($a['submits']['pointsCount'][3] > $b['submits']['pointsCount'][3]) {
                return 1;
            } elseif ($a['submits']['pointsCount'][3] < $b['submits']['pointsCount'][3]) {
                return -1;
            }

            # coefficients
            $ac = FOFCategoryProcessing::getCoefficientAvg($a['team']->getPersons(), $a['team']->event);
            $bc = FOFCategoryProcessing::getCoefficientAvg($b['team']->getPersons(), $b['team']->event);

            if ($ac < $bc) {
                return 1;
            } elseif ($ac > $bc) {
                return -1;
            }

            # team ids
            if ($a['team']->fyziklani_team_id < $b['team']->fyziklani_team_id) {
                return 1;
            } elseif ($a['team']->fyziklani_team_id > $b['team']->fyziklani_team_id) {
                return -1;
            }

            # in case everything fails (at least team ids should be different)
            return 0;
        };
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
     * @return array[]|int[]
     */
    protected function getAllSubmits(TeamModel2 $team): array
    {
        $arraySubmits = [];
        $sum = 0;
        $count = 0;
        $submitPointsCount = [];
        /** @var SubmitModel $submit */
        foreach ($team->getSubmits() as $submit) {
            if ($submit->points === null) {
                continue;
            }
            if (isset($submitPointsCount[$submit->points])) {
                $submitPointsCount[strval($submit->points)]++;
            } else {
                $submitPointsCount[strval($submit->points)] = 1;
            }
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
            'average' => $sum / $count,
            'pointsCount' => $submitPointsCount
        ];
    }
}
