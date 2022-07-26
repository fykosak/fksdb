<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Ranking;

use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Nette\Database\Table\GroupedSelection;
use Nette\SmartObject;
use Nette\Utils\Html;

class RankingStrategy
{
    use SmartObject;

    private TeamService2 $teamService;
    private ModelEvent $event;

    public function __construct(ModelEvent $event, TeamService2 $teamService)
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
                $this->teamService->updateModel($team, ['rank_total' => $rank]);
            } else {
                $this->teamService->updateModel($team, ['rank_category' => $rank]);
            }
            $log->addHtml(
                Html::el('li')
                    ->addText(
                        _('Team') . $team->name . ':(' . $team->fyziklani_team_id . ')' . _(
                            'Rank'
                        ) . ': ' . ($rank)
                    )
            );
        }
        return $log;
    }

    /**
     * @return array[]
     * @throws NotClosedTeamException
     */
    private function getTeamsStats(GroupedSelection $teams): array
    {
        $teamsData = [];
        foreach ($teams as $row) {
            $team = TeamModel2::createFromActiveRow($row, $this->event->mapper);
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
            if ($a['points'] > $b['points']) {
                return 1;
            } elseif ($a['points'] < $b['points']) {
                return -1;
            } else {
                if ($a['submits']['count'] && $b['submits']['count']) {
                    $qa = $a['submits']['sum'] / $a['submits']['count'];
                    $qb = $b['submits']['sum'] / $b['submits']['count'];
                    return (int)($qa - $qb);
                }
                return 0;
            }
        };
    }

    private function getAllTeams(?TeamCategory $category = null): GroupedSelection
    {
        $query = $this->event->getParticipatingFyziklaniTeams();
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
        foreach ($team->getAllSubmits() as $row) {
            $submit = SubmitModel::createFromActiveRow($row, $team->mapper);
            if ($submit->points !== null) {
                $sum += $submit->points;
                $count++;
                $arraySubmits[] = [
                    'task_id' => $submit->fyziklani_task_id,
                    'points' => $submit->points,
                    'time' => $submit->modified,
                ];
            }
        }
        return ['data' => $arraySubmits, 'sum' => $sum, 'count' => $count];
    }
}
