<?php

namespace FyziklaniModule;

use FKSDB\Components\React\Fyziklani\Results;
use Nette\DateTime;

class ResultsPresenter extends BasePresenter {
    use \ReactRequest;

    /**
     * @throws \Nette\Application\ForbiddenRequestException
     */
    protected function unauthorizedAccess() {
        switch ($this->getAction()) {
            case 'default':
            case 'resultsView':
            case 'taskStatistics':
            case 'teamStatistics':
                return;
            default:
                parent::unauthorizedAccess();
        }
    }

    public function requiresLogin() {
        switch ($this->getAction()) {
            case 'default':
            case 'resultsView':
            case 'taskStatistics':
            case 'teamStatistics':
                return false;
            default:
                return true;
        }
    }

    public function titleDefault() {
        $this->setTitle(_('Výsledky a statistiky FYKOSího Fyziklání'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleResultsView() {
        $this->setTitle(_('Výsledky FYKOSího Fyziklání'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleResultsPresentation() {
        $this->setIcon('fa fa-table');
        return $this->setTitle(_('Presentace FYKOSího Fyziklání'));
    }

    public function titleTeamStatistics() {
        $this->setTitle(_('Tímové statistiky FYKOSího Fyzikláni'));
        $this->setIcon('fa fa-line-chart');
    }

    public function titleTaskStatistics() {
        $this->setTitle(_('Statistiky úloh FYKOSího Fyzikláni'));
        $this->setIcon('fa fa-pie-chart');
    }

    public function authorizedDefault() {
        $this->setAuthorized(true);
    }

    public function authorizedResultsView() {
        $this->authorizedDefault();
    }

    public function authorizedTaskStatistics() {
        $this->authorizedDefault();
    }

    public function authorizedTeamStatistics() {
        $this->authorizedDefault();
    }

    public function authorizedResultsPresentation() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani', 'presentation'));
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderResultsView() {
        /*  if ($this->isAjax()) {
              $this->handleAjaxCall();
          }*/
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderResultsPresentation() {
        /* if ($this->isAjax()) {
             $this->handleAjaxCall();
         }*/
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderTeamStatistics() {
        /*  if ($this->isAjax()) {
              $this->handleAjaxCall();
          }*/
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderTaskStatistics() {
        /* if ($this->isAjax()) {
             $this->handleAjaxCall();
         }*/
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    private function handleAjaxCall() {
        $isOrg = $this->getEventAuthorizator()->isAllowed('fyziklani', 'results', $this->getEvent());
        /**
         * @var DateTime $lastUpdated
         */
        $request = $this->getReactRequest();
        $requestData = $request->requestData;
        $lastUpdated = $requestData ? $requestData['lastUpdated'] : null;
        $response = new \ReactResponse();
        $response->setAct('results-update');

        $result = [
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'gameStart' => (string)$this->getEvent()->getParameter('gameStart'),
            'gameEnd' => (string)$this->getEvent()->getParameter('gameEnd'),
            'times' => [
                'toStart' => strtotime($this->getEvent()->getParameter('gameStart')) - time(),
                'toEnd' => strtotime($this->getEvent()->getParameter('gameEnd')) - time(),
                'visible' => $this->isResultsVisible(),
            ],
            'lastUpdated' => (new DateTime())->__toString(),
            'isOrg' => $isOrg,
            'refreshDelay' => $this->getEvent()->getParameter('refreshDelay'),
            'submits' => [],
        ];

        if ($isOrg || $this->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->getSubmits($this->getEventId(), $lastUpdated);
        }
        //  if (!$lastUpdated) {
        $result['rooms'] = $this->getRooms();
        $result['teams'] = $this->serviceFyziklaniTeam->getTeams($this->getEventId());
        $result['tasks'] = $this->serviceFyziklaniTask->getTasks($this->getEventId());
        $result['categories'] = ['A', 'B', 'C'];
        // }

        $response->setData($result);

        $this->sendResponse($response);
    }

    public function createComponentResultsView() {
        return new Results('results-view');
    }

    public function createComponentResultsPresentation() {
        return new Results('results-presentation');
    }

    public function createComponentTeamStatistics() {
        return new Results('team-statistics');
    }

    public function createComponentTaskStatistics() {
        return new Results('task-statistics');
    }

    /**
     * @return boolean
     */
    private function isResultsVisible() {
        $hardDisplay = $this->getEvent()->getParameter('resultsHardDisplay');
        $before = (time() < strtotime($this->getEvent()->getParameter('resultsHide')));
        $after = (time() > strtotime($this->getEvent()->getParameter('resultsDisplay')));

        return $hardDisplay || ($before && $after);
    }
}
