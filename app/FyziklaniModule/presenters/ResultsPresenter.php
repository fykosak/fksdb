<?php

namespace FyziklaniModule;

use Nette\Application\Responses\JsonResponse;
use Nette\DateTime;
use Nette\Diagnostics\FireLogger;

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
            $isOrg = $this->getEventAuthorizator()->isAllowed('fyziklani', 'results', $this->getCurrentEvent());
            /**
             * @var DateTime $lastUpdated
             */
            $lastUpdated = $this->getHttpRequest()->getQuery('lastUpdated');

            $result = [];
            $result['lastUpdated'] = (new DateTime())->__toString();
            if (!$lastUpdated) {
                $result['tasks'] = $this->serviceFyziklaniTask->getTasks($this->eventID);
                $result['teams'] = $this->serviceFyziklaniTeam->getTeams($this->eventID);
            }
            $result['submits'] = [];
            $result['isOrg'] = $isOrg;
            if ($isOrg || $this->isResultsVisible()) {
                $result['submits'] = $this->serviceFyziklaniSubmit->getSubmits($this->eventID, $lastUpdated);
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
