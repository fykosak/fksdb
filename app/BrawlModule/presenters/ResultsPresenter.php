<?php

namespace BrawlModule;

use Nette\Application\Responses\JsonResponse;
use Nette\DateTime;
use ORM\Models\Events\ModelFyziklaniTeam;

class ResultsPresenter extends BasePresenter {
    
    protected function unauthorizedAccess() {
        if ($this->getAction() === 'default') {
            return;
        }

        parent::unauthorizedAccess();
    }

    public function requiresLogin() {
        return $this->getAction() !== 'default';
    }

    public function renderDefault() {
        if ($this->isAjax()) {
            $isOrg = $this->getEventAuthorizator()->isAllowed('brawl', 'results', $this->getEvent());
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
            $result['refreshDelay'] = $this->getEvent()->getParameter('refreshDelay');
            $result['times'] = [
                'toStart' => strtotime($this->getEvent()->getParameter('gameStart')) - time(),
                'toEnd' => strtotime($this->getEvent()->getParameter('gameEnd')) - time(),
                'visible' => $this->isResultsVisible()
            ];
            $this->sendResponse(new JsonResponse($result));
        }
    }

    private function getTasks() {
        $tasks = [];
        /**
         * @var $row \ModelBrawlTask
         */
        foreach ($this->serviceBrawlTask->findAll($this->getEventId())->order('label') as $row) {
            $tasks[] = [
                'label' => $row->label,
                'task_id' => $row->fyziklani_task_id
            ];
        }
        return $tasks;
    }

    private function getTeams() {
        $teams = [];
        /**
         * @var $row ModelFyziklaniTeam
         */
        foreach ($this->serviceBrawlTeam->findParticipating($this->getEventId()) as $row) {
             $teams[] = [
                'category' => $row->category,
                'room' => $row->room,
                'name' => $row->name,
                'team_id' => $row->e_fyziklani_team_id,
            ];
        }
        return $teams;
    }

    private function getSubmits($lastUpdated = null) {
        $query = $this->serviceBrawlSubmit->getTable()->where('e_fyziklani_team.event_id', $this->getEventId());
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        /**
         * @var $submit \ModelBrawlSubmit
         */
        foreach ($query as $submit) {
            $submits[$submit->fyziklani_submit_id] = [
                'points' => $submit->points,
                'team_id' => $submit->e_fyziklani_team_id,
                'task_id' => $submit->fyziklani_task_id
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
        $hardDisplay = $this->getEvent()->getParameter('resultsHardDisplay');
        $before = (time() < strtotime($this->getEvent()->getParameter('resultsHide')));
        $after = (time() > strtotime($this->getEvent()->getParameter('resultsDisplay')));

        return $hardDisplay || ($before && $after);
    }
}