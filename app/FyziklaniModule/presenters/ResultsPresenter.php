<?php

namespace FyziklaniModule;

use Nette\Application\Responses\JsonResponse;
use Nette\DateTime;

class ResultsPresenter extends BasePresenter {

    protected function unauthorizedAccess() {
        if ($this->getAction() == 'default') {
            return;
        }

        parent::unauthorizedAccess();
    }

    public function requiresLogin() {
        return $this->getAction() != 'default';
    }

    public function renderDefault() {
        if ($this->isAjax()) {
            $isOrg = $this->getEventAuthorizator()->isAllowed('fyziklani', 'results', $this->getCurrentEvent());
            /**
             * @var DateTime $lastUpdated
             */
            $lastUpdated = $this->getHttpRequest()->getQuery('lastUpdated');

            $result = [];
            $result['lastUpdated'] = (new DateTime())->__toString();
            if (!$lastUpdated) {
                $result['tasks'] = $this->getTasks();
                $result['teams'] = $this->getTeams();
            }
            $result['submits'] = [];
            $result['isOrg'] = $isOrg;
            if ($isOrg || $this->isResultsVisible()) {
                $result['submits'] = $this->getSubmits($lastUpdated);
            }
            $result['refreshDelay'] = $this->getCurrentEvent()->getParameter('refreshDelay');
            $result['times'] = [
                'gameStart' => $this->getCurrentEvent()->getParameter('gameStart')->__toString(),
                'gameEnd' => $this->getCurrentEvent()->getParameter('gameEnd')->__toString(),
                'toStart' => strtotime($this->getCurrentEvent()->getParameter('gameStart')) - time(),
                'toEnd' => strtotime($this->getCurrentEvent()->getParameter('gameEnd')) - time(),
                'visible' => $this->isResultsVisible()
            ];
            $this->sendResponse(new JsonResponse($result));
        }
    }

    private function getTasks() {
        $tasks = [];
        foreach ($this->serviceFyziklaniTask->findAll($this->eventID)->order('label') as $row) {
            $tasks[] = [
                'label' => $row->label,
                'task_id' => $row->fyziklani_task_id
            ];
        }
        return $tasks;
    }

    private function getTeams() {
        $teams = [];
        foreach ($this->serviceFyziklaniTeam->findParticipating($this->eventID) as $row) {
            $teams[] = [
                'category' => $row->category,
                'room' => $row->room,
                'name' => $row->name,
                'team_id' => $row->e_fyziklani_team_id
            ];
        }
        return $teams;
    }

    private function getSubmits($lastUpdated = null) {
        $query = $this->serviceFyziklaniSubmit->getTable()->where('e_fyziklani_team.event_id', $this->eventID);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        foreach ($query as $submit) {
            $submits[$submit->fyziklani_submit_id] = [
                'points' => $submit->points,
                'team_id' => $submit->e_fyziklani_team_id,
                'task_id' => $submit->fyziklani_task_id,
                'created' => $submit->created->format(\DateTime::ISO8601)
            ];
        }
        return $submits;
    }

    public function titleDefault() {
        $this->setTitle(_('Výsledky FYKOSího Fyziklání'));
    }

    public function authorizedDefault() {
        $this->setAuthorized(true);
    }

    private function isResultsVisible() {
        $hardDisplay = $this->getCurrentEvent()->getParameter('resultsHardDisplay');
        $before = (time() < strtotime($this->getCurrentEvent()->getParameter('resultsHide')));
        $after = (time() > strtotime($this->getCurrentEvent()->getParameter('resultsDisplay')));

        return $hardDisplay || ($before && $after);
    }
}