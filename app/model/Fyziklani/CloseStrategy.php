<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Database\Table\Selection;
use Nette\Utils\Html;
use Traversable;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class CloseStrategy {
    /**
     * @var BasePresenter
     * @deprecated
     */
    protected $presenter;

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ModelEvent
     */
    private $event;

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
     * @throws BadRequestException
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
     * @throws BadRequestException
     */
    public function __invoke(string $category = null): Html {
        return $this->close($category);
    }

    /**
     * @param Traversable|array $data
     * @param $total
     * @return Html
     */
    private function saveResults(array $data, bool $total): Html {
        $log = Html::el('ul');
        foreach ($data as $index => $teamData) {
            /**
             * @var ModelFyziklaniTeam $team
             */
            $team = $teamData['team'];
            if ($total) {
                $team->update(['rank_total' => $index + 1]);
            } else {
                $team->update(['rank_category' => $index + 1]);
            }
            $log->addHtml(Html::el('li')
                ->addText(_('Team') . $team->name . ':(' . $team->e_fyziklani_team_id . ')' . _('Pořadí') . ': ' . ($index + 1)));
        }
        return $log;
    }

    /**
     * @param Selection $teams
     * @return array[]
     * @throws BadRequestException
     */
    private function getTeamsStats(Selection $teams): array {
        $teamsData = [];
        foreach ($teams as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);

            if ($team->hasOpenSubmitting()) {
                throw new BadRequestException('Tým ' . $team->name . '(' . $team->e_fyziklani_team_id . ') nemá uzavřené bodování');
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
     * @return callable
     */
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
     * @return Selection
     */
    private function getAllTeams(string $category = null): Selection {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->event);
        if ($category) {
            $query->where('category', $category);
        }
        return $query;
    }

    /**
     * @param ModelFyziklaniTeam $team
     * @return array
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
                    'time' => $submit->modified
                ];
            }
        }
        return ['data' => $arraySubmits, 'sum' => $sum, 'count' => $count];
    }
}
