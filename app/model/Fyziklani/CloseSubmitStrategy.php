<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace FKSDB\model\Fyziklani;

use Nette\Application\BadRequestException;
use OrgModule\FyziklaniPresenter;
use ORM\Services\Events\ServiceFyziklaniTeam;

/**
 * Description of CloseSubmitStrategy
 *
 * @author miso
 */
class CloseSubmitStrategy {
    /**
     * @var string
     */
    protected $category;
    /**
     * @var FyziklaniPresenter
     */
    protected $presenter;
    
    /**
     *
     * @var ServiceFyziklaniTeam 
     */
    private $serviceFyziklaniTeam;
    
    private $eventID;


    public function __construct($eventID, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->eventID = $eventID;
    }

    public function closeByCategory($category, &$msg = null) {
        $connection = $this->serviceFyziklaniTeam->getConnection();
        $connection->beginTransaction();
        
        $teams = $this->getAllTeams($category);
        $teamsData = $this->getTeamsStats($teams);
        usort($teamsData, self::sortFunction());
        $this->saveResults($teamsData, $msg);
        
        $connection->commit();
    }

    public function closeGlobal(&$msg = null) {
        $connection = $this->serviceFyziklaniTeam->getConnection();
        $connection->beginTransaction();
        
        $teams = $this->getAllTeams(null);
        $teamsData = $this->getTeamsStats($teams);
        usort($teamsData, self::sortFunction());
        $this->saveResults($teamsData, $msg);
        
        $connection->commit();
    }

    private function saveResults($data, &$msg = null) {
        $msg = '';
        foreach ($data as $index => &$teamData) {
            $teamData['rank_category'] = $index;
            $team = $this->serviceFyziklaniTeam->findByPrimary($teamData['e_fyziklani_team_id']);
            $this->serviceFyziklaniTeam->updateModel($team, ['rank_category', $index + 1]);
            $this->serviceFyziklaniTeam->save($team);
            $msg .= '<li>TeamID:' . $teamData['e_fyziklani_team_id'] . ' Poradie: ' . ($index + 1) . '</li>';
        }
    }

    private function getTeamsStats($teams) {
        $teamsData = [];
        foreach ($teams as $team) {
            $teamData = [];
            $team_id = $team->e_fyziklani_team_id;
            $teamData['e_fyziklani_team_id'] = $team_id;
            if ($team->points === null) {
                throw new BadRequestException('Tým ' . $team->name . '(' . $team_id . ') nemá uzatvorené bodovanie');
            }
            $teamData['points'] = $team->points;
            $teamData['submits'] = $this->getAllSubmits($team_id);
            $teamsData[] = $teamData;
        }
        return $teamsData;
    }

    private static function sortFunction() {
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
        $query = $this->serviceFyziklaniTeam->findParticipating($this->eventID);
        if ($category) {
            $query->where('category', $category);
        }
        return $query;
    }

    protected function getAllSubmits($team_id) {
        $submits = $this->serviceFyziklaniTeam->findByPrimary($team_id)->getSubmits();
        $arraySubmits = [];
        $sum = 0;
        $count = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
            $count++;
            $arraySubmits[] = ['task_id' => $submit->task_id, 'points' => $submit->points, 'time' => $submit->submitted_on];
        }
        return ['data' => $arraySubmits, 'sum' => $sum, 'count' => $count];
    }
}
