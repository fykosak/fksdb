<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Ranking;

use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService;
use Nette\Database\Table\GroupedSelection;
use Nette\SmartObject;
use Nette\Utils\Html;

class RankingStrategy
{
    use SmartObject;

    private TeamService $teamService;
    private ModelEvent $event;

    public function __construct(ModelEvent $event, TeamService $teamService)
    {
        $this->teamService = $teamService;
        $this->event = $event;
    }

    /**
     * @throws NotClosedTeamException
     */
    public function close(?string $category = null): Html
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
            /** @var TeamModel $team */
            $team = $teamData['team'];
            if ($total) {
                $this->teamService->updateModel($team, ['rank_total' => $index + 1]);
            } else {
                $this->teamService->updateModel($team, ['rank_category' => $index + 1]);
            }
            $log->addHtml(
                Html::el('li')
                    ->addText(
                        _('Team') . $team->name . ':(' . $team->e_fyziklani_team_id . ')' . _(
                            'Rank'
                        ) . ': ' . ($index + 1)
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
            $team = TeamModel::createFromActiveRow($row);
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
                    return $qa - $qb;
                }
                return 0;
            }
        };
    }

    private function getAllTeams(?string $category = null): GroupedSelection
    {
        $query = $this->event->getParticipatingTeams();
        if ($category) {
            $query->where('category', $category);
        }
        return $query;
    }

    /**
     * @return array[]|int[]
     */
    protected function getAllSubmits(TeamModel $team): array
    {
        $arraySubmits = [];
        $sum = 0;
        $count = 0;
        foreach ($team->getAllSubmits() as $row) {
            $submit = SubmitModel::createFromActiveRow($row);
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
