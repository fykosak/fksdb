<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Fyziklani;

use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Html;
use OrgModule\FyziklaniPresenter;

/**
 * Description of CloseSubmitStragegy
 *
 * @author miso
 */
class CloseSubmitStragegy {
    /**
     * @var string
     */
    protected $category;
    /**
     * @var FyziklaniPresenter
     */
    protected $presenter;

    public function __construct(FyziklaniPresenter $presenter, $category = null) {
        $this->presenter = $presenter;
        $this->category = $category;
    }

    public function preprocess() {
        $teams = $this->getAllTeams($this->category);
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
        usort($teamsData, self::sortFunction());
        $msg = '';
        foreach ($teamsData as $index => &$teamData) {
            $teamData['rank_category'] = $index;
            $msg .= '<li>TeamID:' . $teamData['e_fyziklani_team_id'] . ' Poradie: ' . ($index + 1) . '</li>';
            $this->presenter->database->query('UPDATE ' . \DbNames::TAB_E_FYZIKLANI_TEAM . ' SET rank_category=? WHERE e_fyziklani_team_id=?', $index + 1, $teamData['e_fyziklani_team_id']);
        }
        $this->presenter->flashMessage(Html::el()->add('poradie bolo uložené' . Html::el('ul')->add($msg)), 'success');
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
        $database = $this->presenter->database;
        $query = $database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->where('status', 'participated')->where('event_id', $this->presenter->eventID);
        if ($category) {
            $query->where('category', $category);
        }
        return $query;
    }

    protected function getAllSubmits($team_id) {
        $submits = $this->presenter->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id', $team_id);
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
