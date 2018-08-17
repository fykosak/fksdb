<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\Results;
use Nette\DateTime;

class ResultsPresenter extends BasePresenter {
    /**
     * @throws \Nette\Application\ForbiddenRequestException
     */
    protected function unauthorizedAccess() {
        switch ($this->getAction()) {
            case 'default':
            case 'results':
            case 'statistics':
                return;
            default:
                parent::unauthorizedAccess();
        }
    }

    public function requiresLogin() {
        switch ($this->getAction()) {
            case 'default':
            case 'results':
            case 'statistics':
                return false;
            default:
                return true;
        }
    }

    public function titleDefault() {
        $this->setTitle(_('Výsledky FYKOSího Fyziklání'));
    }

    public function titleResults() {
        return $this->titleDefault();
    }

    public function titleStatistics() {
        $this->setTitle(_('Statistiky FYKOSího Fyzikláni'));
    }

    public function authorizedDefault() {
        $this->setAuthorized(true);
    }

    public function authorizedResults() {
        $this->setAuthorized(true);
    }

    public function authorizedStatistics() {
        $this->setAuthorized(true);
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderResultsView() {
        if ($this->isAjax()) {
            $this->handleAjaxCall();
        }
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderResultsPresentation() {
        if ($this->isAjax()) {
            $this->handleAjaxCall();
        }
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderTeamStatistics() {
        if ($this->isAjax()) {
            $this->handleAjaxCall();
        }
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderTaskStatistics() {
        if ($this->isAjax()) {
            $this->handleAjaxCall();
        }
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    private function handleAjaxCall() {
        $isOrg = $this->getEventAuthorizator()->isAllowed('fyziklani', 'results', $this->getEvent());
        /**
         * @var DateTime $lastUpdated
         */
        $lastUpdated = $this->getHttpRequest()->getQuery('lastUpdated');
        $response = new \ReactResponse();
        $response->setAct('results-update');

        $result = [
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'gameStart' => (string)$this->getEvent()->getParameter('gameStart'),
            'gameEnd' => (string)$this->getEvent()->getParameter('gameEnd'),
        ];
        $result['lastUpdated'] = (new DateTime())->__toString();
        $result['submits'] = [];
        $result['isOrg'] = $isOrg;
        if ($isOrg || $this->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->getSubmits($this->getEventId(), $lastUpdated);
        }
        $result['refreshDelay'] = $this->getEvent()->getParameter('refreshDelay');
        $result['times'] = [
            'toStart' => strtotime($this->getEvent()->getParameter('gameStart')) - time(),
            'toEnd' => strtotime($this->getEvent()->getParameter('gameEnd')) - time(),
            'visible' => $this->isResultsVisible()
        ];
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
