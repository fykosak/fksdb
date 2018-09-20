<?php

namespace FKSDB\Components\React\Fyziklani\ResultsAndStatistics;

use FKSDB\Components\React\Fyziklani\FyziklaniModule;
use ORM\Services\Events\ServiceFyziklaniTeam;

abstract class ResultsAndStatistics extends FyziklaniModule {

    /**
     *
     * @var ServiceFyziklaniTeam
     */
    protected $serviceFyziklaniTeam;

    /**
     *
     * @var \ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    /**
     *
     * @var \ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    public function __construct(
        $mode,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        \ServiceFyziklaniTask $serviceFyziklaniTask,
        \ServiceBrawlRoom $serviceBrawlRoom,
        \ServiceBrawlTeamPosition $serviceBrawlTeamPosition,
        \ModelEvent $event
    ) {
        parent::__construct($serviceBrawlRoom, $event);
         $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
         $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    public final function getData() {
        return null;
    }

    protected function getActions() {
        $actions = parent::getActions();
        return $actions['refresh'] = $this->link('refresh!');

    }

    public function handleRefresh() {
        $presenter = $this->getPresenter();

        $isOrg = $presenter->getEventAuthorizator()->isAllowed('fyziklani', 'results', $this->getEvent());
        /**
         * @var \DateTime $lastUpdated
         */
        $request = $this->getReactRequest();
        $requestData = $request->requestData;
        $lastUpdated = $requestData ? $requestData['lastUpdated'] : null;
        $response = new \ReactResponse();
        $response->setAct('results-update');

        $result = [
            'basePath' => $presenter->getHttpRequest()->getUrl()->getBasePath(),
            'gameStart' => (string)$this->getEvent()->getParameter('gameStart'),
            'gameEnd' => (string)$this->getEvent()->getParameter('gameEnd'),
            'times' => [
                'toStart' => strtotime($this->getEvent()->getParameter('gameStart')) - time(),
                'toEnd' => strtotime($this->getEvent()->getParameter('gameEnd')) - time(),
                'visible' => $this->isResultsVisible(),
            ],
            'lastUpdated' => (new \DateTime())->__toString(),
            'isOrg' => $isOrg,
            'refreshDelay' => $this->getEvent()->getParameter('refreshDelay'),
            'submits' => [],
        ];

        if ($isOrg || $this->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->getSubmits($this->getEvent()->event_id, $lastUpdated);
        }
        //  if (!$lastUpdated) {
        $result['rooms'] = $this->getRooms();
        $result['teams'] = $this->serviceFyziklaniTeam->getTeams($this->getEvent()->event_id);
        $result['tasks'] = $this->serviceFyziklaniTask->getTasks($this->getEvent()->event_id);
        $result['categories'] = ['A', 'B', 'C'];
        // }

        $response->setData($result);

        $this->sendResponse($response);
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
