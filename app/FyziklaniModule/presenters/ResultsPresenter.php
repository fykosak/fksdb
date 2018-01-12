<?php

namespace FyziklaniModule;

use BrawlLib\Components\Results;
use Nette\Application\Responses\JsonResponse;
use Nette\DateTime;
use ORM\Models\Events\ModelFyziklaniTeam;

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
            if (!$lastUpdated) {
                $result['tasks'] = $this->serviceFyziklaniTask->getTasks($this->getEventId());
                $result['teams'] = $this->serviceFyziklaniTeam->getTeams($this->getEventId());
            }
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
