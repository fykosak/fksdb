<?php

namespace BrawlModule;

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
            $isOrg = $this->getEventAuthorizator()->isAllowed('brawl', 'results', $this->getCurrentEvent());
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
                'toStart' => strtotime($this->getCurrentEvent()->getParameter('gameStart')) - time(),
                'toEnd' => strtotime($this->getCurrentEvent()->getParameter('gameEnd')) - time(),
                'visible' => $this->isResultsVisible()
            ];
            $this->sendResponse(new JsonResponse($result));
        }
    }

    private function getTasks() {
        $tasks = [];
        foreach ($this->serviceBrawlTask->findAll($this->eventID)->order('label') as $row) {
            $tasks[] = [
                'label' => $row->label,
                'task_id' => $row->brawl_task_id
            ];
        }
        return $tasks;
    }

    private function getTeams() {
        $teams = [];
        foreach ($this->serviceBrawlTeam->findParticipating($this->eventID) as $row) {
            $teams[] = [
                'category' => $row->category,
                'room' => $row->room,
                'name' => $row->name,
                'team_id' => $row->e_brawl_team_id
            ];
        }
        return $teams;
    }

    private function getSubmits($lastUpdated = null) {
        $query = $this->serviceBrawlSubmit->getTable()->where('e_brawl_team.event_id', $this->eventID);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        foreach ($query as $submit) {
            $submits[$submit->brawl_submit_id] = [
                'points' => $submit->points,
                'team_id' => $submit->e_brawl_team_id,
                'task_id' => $submit->brawl_task_id
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