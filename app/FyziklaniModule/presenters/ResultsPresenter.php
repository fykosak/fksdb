<?php

namespace FyziklaniModule;

use BrawlLib\Components\Results;
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
        return $this->getAction() !== 'default';
    }

    public function renderDefault() {

        if ($this->isAjax()) {
            $isOrg = $this->getEventAuthorizator()->isAllowed('fyziklani', 'results', $this->getEvent());
            /**
             * @var DateTime $lastUpdated
             */
            $lastUpdated = $this->getHttpRequest()->getQuery('lastUpdated');

            $result = [];
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
            $this->sendResponse(new JsonResponse($result));
        }
    }

    public function createComponentResults() {
        $control = new Results();
// TODO set others parameters (game start/end...)
        $control->setRooms($this->getRooms());
        $control->setTeams($this->serviceFyziklaniTeam->getTeams($this->getEventId()));
        $control->setTasks($this->serviceFyziklaniTask->getTasks($this->getEventId()));

        $control->setBasePath($this->getHttpRequest()->getUrl()->getBasePath());
        return $control;
    }

    public function titleDefault() {
        $this->setTitle(_('Výsledky FYKOSího Fyziklání'));
    }

    public function authorizedDefault() {
        $this->setAuthorized(true);
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
