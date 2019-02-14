<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\ModelEvent;
use FyziklaniModule\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Database\Table\Selection;
use Nette\Utils\Html;
use ORM\Models\Events\ModelFyziklaniTeam;
use ORM\Services\Events\ServiceFyziklaniTeam;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class CloseSubmitStrategy {
    /**
     * @var BasePresenter
     * @deprecated
     */
    protected $presenter;

    /**
     *
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
     * @param $category
     * @param null $msg
     * @throws BadRequestException
     */
    public function closeByCategory($category, &$msg = null) {
        $total = is_null($category);
        $connection = $this->serviceFyziklaniTeam->getConnection();
        $connection->beginTransaction();
        $teams = $this->getAllTeams($category);
        $teamsData = $this->getTeamsStats($teams);
        usort($teamsData, self::getSortFunction());
        $this->saveResults($teamsData, $total, $msg);
        $connection->commit();
    }

    /**
     * @param null $msg
     * @throws BadRequestException
     */
    public function closeGlobal(&$msg = null) {
        $this->closeByCategory(null, $msg);
    }

    /**
     * @param $data
     * @param $total
     * @param null $msg
     */
    private function saveResults($data, $total, &$msg = null) {
        $msg = '';
        foreach ($data as $index => &$teamData) {
            $team = ModelFyziklaniTeam::createFromTableRow($this->serviceFyziklaniTeam->findByPrimary($teamData['e_fyziklani_team_id']));
            if ($total) {
                $this->serviceFyziklaniTeam->updateModel($team, ['rank_total' => $index + 1]);
            } else {
                $this->serviceFyziklaniTeam->updateModel($team, ['rank_category' => $index + 1]);
            }
            $this->serviceFyziklaniTeam->save($team);
            $msg .= Html::el('li')
                ->add(_('TeamID') . ':' . $teamData['e_fyziklani_team_id'] . _('Pořadí') . ': ' . ($index + 1));
        }
    }

    /**
     * @param $teams
     * @return array
     * @throws BadRequestException
     */
    private function getTeamsStats($teams): array {
        $teamsData = [];
        foreach ($teams as $row) {
            $team = ModelFyziklaniTeam::createFromTableRow($row);
            $teamData = [];
            $team_id = $team->e_fyziklani_team_id;
            $teamData['e_fyziklani_team_id'] = $team_id;
            if ($team->points === null) {
                throw new BadRequestException('Tým ' . $team->name . '(' . $team_id . ') nemá uzavřené bodování');
            }
            $teamData['points'] = $team->points;
            $teamData['submits'] = $this->getAllSubmits($team_id);
            $teamsData[] = $teamData;
        }
        return $teamsData;
    }

    /**
     * @return \Closure
     */
    private static function getSortFunction(): \Closure {
        return function ($b, $a) {
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
     * @param null $category
     * @return Selection
     */
    private function getAllTeams($category = null): Selection {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->event);
        if ($category) {
            $query->where('category', $category);
        }
        return $query;
    }

    /**
     * @param integer $teamId
     * @return array
     */
    protected function getAllSubmits(int $teamId): array {
        $team = ModelFyziklaniTeam::createFromTableRow($this->serviceFyziklaniTeam->findByPrimary($teamId));
        $arraySubmits = [];
        $sum = 0;
        $count = 0;
        foreach ($team->getSubmits() as $row) {
            $submit = \ModelFyziklaniSubmit::createFromTableRow($row);
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
