<?php
/**
 * Created by PhpStorm.
 * User: miso
 * Date: 3.12.2016
 * Time: 1:09
 */

namespace FyziklaniModule;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;

class ResultsPresenter extends BasePresenter {

    public function renderDefault() {
        if ($this->isAjax()) {
            $result = [];
            $type = $this->getHttpRequest()->getQuery('type');

            if ($type == 'init') {
                foreach ($this->database->table(\DbNames::TAB_FYZIKLANI_TASK)->where('event_id', $this->eventID)->order('label') as $row) {
                    $result['tasks'][] = ['label' => $row->label, 'name' => $row->name, 'task_id' => $row->fyziklani_task_id];
                }
                foreach ($this->database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->where('event_id', $this->eventID) as $row) {
                    $result['teams'][] = ['category' => $row->category, 'room' => $row->room, 'name' => $row->name, 'team_id' => $row->e_fyziklani_team_id];
                }
            } elseif ($type == 'refresh') {
                $result['submits'] = [];
                $isOrg = $this->getContestAuthorizator()->isAllowedEvent('fyziklani', 'results', $this->getCurrentEvent(), $this->database);
                $result['is_org'] = $isOrg;
                if ($isOrg) {
                    $submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team.event_id', $this->eventID);
                    foreach ($submits as $submit) {
                        $result['submits'][] = ['points' => $submit->points, 'team_id' => $submit->e_fyziklani_team_id, 'task_id' => $submit->fyziklani_task_id];
                    }
                }
            } else {
                throw new BadRequestException('error', 404);
            }
            $result['times'] = ['toStart' => strtotime($this->container->parameters['fyziklani']['start']) - time(), 'toEnd' => strtotime($this->container->parameters['fyziklani']['end']) - time(), 'visible' => $this->isResultsVisible()];
            $this->sendResponse(new JsonResponse($result));
        }
    }

    public function titleDefault() {
        $this->setTitle(_('Výsledky Fykosího Fyzikláni'));
    }

    public function authorizedDefault() {
         $this->setAuthorized(true);
    }



    private function isResultsVisible() {
        return (time() < strtotime($this->container->parameters['fyziklani']['results']['hidde'])) && (time() > strtotime($this->container->parameters['fyziklani']['results']['display']));
    }
}