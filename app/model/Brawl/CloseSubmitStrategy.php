<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace FKSDB\model\Brawl;

use Nette\Application\BadRequestException;
use BrawlModule\BasePresenter;
use Nette\Utils\Html;
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
    private $serviceBrawlTeam;
    /**
     * @var int
     */
    private $eventID;


    public function __construct($eventID, ServiceFyziklaniTeam $serviceBrawlTeam) {
        $this->serviceBrawlTeam = $serviceBrawlTeam;
        $this->eventID = $eventID;
    }

    public function closeByCategory($category, &$msg = null) {
        $total = is_null($category);
        $connection = $this->serviceBrawlTeam->getConnection();
        $connection->beginTransaction();
        $teams = $this->getAllTeams($category);
        $teamsData = $this->getTeamsStats($teams);
        usort($teamsData, self::getSortFunction());
        $this->saveResults($teamsData, $total, $msg);
        $connection->commit();
    }

    public function closeGlobal(&$msg = null) {
        $this->closeByCategory(null, $msg);
    }

    private function saveResults($data, $total, &$msg = null) {
        $msg = '';
        foreach ($data as $index => &$teamData) {
            $team = $this->serviceBrawlTeam->findByPrimary($teamData['e_brawl_team_id']);
            if ($total) {
                $this->serviceBrawlTeam->updateModel($team, ['rank_total' => $index + 1]);
            } else {
                $this->serviceBrawlTeam->updateModel($team, ['rank_category' => $index + 1]);
            }
            $this->serviceBrawlTeam->save($team);
            $msg .= Html::el('li')
                ->add(_('TeamID') . ':' . $teamData['e_brawl_team_id'] . _('Pořadí') . ': ' . ($index + 1));
        }
    }

    private function getTeamsStats($teams) {
        $teamsData = [];
        foreach ($teams as $team) {
            $teamData = [];
            $team_id = $team->e_brawl_team_id;
            $teamData['e_brawl_team_id'] = $team_id;
            if ($team->points === null) {
                throw new BadRequestException('Tým ' . $team->name . '(' . $team_id . ') nemá uzavřené bodování');
            }
            $teamData['points'] = $team->points;
            $teamData['submits'] = $this->getAllSubmits($team_id);
            $teamsData[] = $teamData;
        }
        return $teamsData;
    }

    private static function getSortFunction() {
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

    private function getAllTeams($category = null) {
        $query = $this->serviceBrawlTeam->findParticipating($this->eventID);
        if ($category) {
            $query->where('category', $category);
        }
        return $query;
    }

    protected function getAllSubmits($team_id) {
        $submits = $this->serviceBrawlTeam->findByPrimary($team_id)->getSubmits();
        $arraySubmits = [];
        $sum = 0;
        $count = 0;
        foreach ($submits as $submit) {
            if ($submit->points !== null) {
                $sum += $submit->points;
                $count++;
                $arraySubmits[] = [
                    'task_id' => $submit->task_id,
                    'points' => $submit->points,
                    'time' => $submit->modified
                ];
            }
        }
        return ['data' => $arraySubmits, 'sum' => $sum, 'count' => $count];
    }
}
