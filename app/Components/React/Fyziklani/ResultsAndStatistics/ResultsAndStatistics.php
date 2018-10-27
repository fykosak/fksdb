<?php

namespace FKSDB\Components\React\Fyziklani\ResultsAndStatistics;

use FKSDB\Components\React\Fyziklani\FyziklaniModule;
use FyziklaniModule\BasePresenter;
use Nette\ArgumentOutOfRangeException;
use Nette\DateTime;

abstract class ResultsAndStatistics extends FyziklaniModule {

    public final function getData(): string {
        return '';
    }

    protected function getActions() {
        $actions = parent::getActions();
        $actions['refresh'] = $this->link('refresh!');
        return $actions;

    }

    public function handleRefresh() {
        $presenter = $this->getPresenter();
        if (!($presenter instanceof BasePresenter)) {
            throw new ArgumentOutOfRangeException();
        }
        $isOrg = $presenter->getEventAuthorizator()->isAllowed('fyziklani', 'results', $this->getEvent());
        /**
         * @var \DateTime $lastUpdated
         */
        $request = $this->getReactRequest();
        $requestData = $request->requestData;
        $lastUpdated = $requestData ? $requestData : null;
        $response = new \ReactResponse();
        $response->setAct('results-update');
        $gameSetup = $this->getEvent()->getParameter('gameSetup');
        $result = [
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'gameStart' => (string)$gameSetup['gameStart'],
            'gameEnd' => (string)$gameSetup['gameEnd'],
            'times' => [
                'toStart' => strtotime($gameSetup['gameStart']) - time(),
                'toEnd' => strtotime($gameSetup['gameEnd']) - time(),
                'visible' => $this->isResultsVisible(),
            ],
            'lastUpdated' => (new DateTime())->__toString(),
            'isOrg' => $isOrg,
            'refreshDelay' => $gameSetup['refreshDelay'],
            'submits' => [],
        ];

        if ($isOrg || $this->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->getSubmits($this->getEvent()->event_id, $lastUpdated);
        }
        //if (!$lastUpdated) {
        $result['rooms'] = $this->getRooms();
        $result['teams'] = $this->serviceFyziklaniTeam->getTeams($this->getEvent()->event_id);
        $result['tasks'] = $this->serviceFyziklaniTask->getTasks($this->getEvent()->event_id);
        $result['categories'] = ['A', 'B', 'C'];
        // }

        $response->setData($result);

        $this->getPresenter()->sendResponse($response);
    }

    /**
     * @return boolean
     */
    private function isResultsVisible() {
        $gameSetup = $this->getEvent()->getParameter('gameSetup');
        $hardDisplay = $gameSetup['resultsHardDisplay'];
        $before = (time() < strtotime($gameSetup['resultsHide']));
        $after = (time() > strtotime($gameSetup['resultsDisplay']));

        return $hardDisplay || ($before && $after);
    }


}
