<?php

namespace FKSDB\Fyziklani\Ranking;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\Modules\FyziklaniModule\BasePresenter;
use Nette\Utils\Html;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class RankingStrategy {
    /**
     * @var BasePresenter
     * @deprecated
     */
    protected $presenter;

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ModelEvent $event;

    /**
     * CloseSubmitStrategy constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
    }

    /**
     * @param string|null $category
     * @return Html
     *
     * @throws NotClosedTeamException
     * @internal
     */
    public function close(string $category = null): Html {
        $connection = $this->serviceFyziklaniTeam->getConnection();
        $connection->beginTransaction();
        $teams = $this->getAllTeams($category);
        $teamsData = $this->getTeamsStats($teams);
        usort($teamsData, self::getSortFunction());
        $log = $this->saveResults($teamsData, is_null($category));
        $connection->commit();
        return $log;
    }

    /**
     * @param string|null $category
     * @return Html
     * @throws NotClosedTeamException
     */
    public function __invoke(string $category = null): Html {
        return $this->close($category);
    }

    private function saveResults(array $data, bool $total): Html {
        $log = Html::el('ul');
        foreach ($data as $index => $teamData) {
            /** @var ModelFyziklaniTeam $team */
            $team = $teamData['team'];
            if ($total) {
                $this->serviceFyziklaniTeam->updateModel2($team, ['rank_total' => $index + 1]);
            } else {
                $this->serviceFyziklaniTeam->updateModel2($team, ['rank_category' => $index + 1]);
            }
            $log->addHtml(Html::el('li')
                ->addText(_('Team') . $team->name . ':(' . $team->e_fyziklani_team_id . ')' . _('Rank') . ': ' . ($index + 1)));
        }
        return $log;
    }

    /**
     * @param TypedTableSelection $teams
     * @return array[]
     * @throws NotClosedTeamException
     */
    private function getTeamsStats(TypedTableSelection $teams): array {
        $teamsData = [];
        /** @var ModelFyziklaniTeam $team */
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

    private static function getSortFunction(): callable {
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

    /**
     * @param string|null $category
     * @return TypedTableSelection
     */
    private function getAllTeams(string $category = null): TypedTableSelection {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->event);
        if ($category) {
            $query->where('category', $category);
        }
        return $query;
    }

    /**
     * @param ModelFyziklaniTeam $team
     * @return array[]|int[]
     */
    protected function getAllSubmits(ModelFyziklaniTeam $team): array {
        $arraySubmits = [];
        $sum = 0;
        $count = 0;
        foreach ($team->getAllSubmits() as $row) {
            $submit = ModelFyziklaniSubmit::createFromActiveRow($row);
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
