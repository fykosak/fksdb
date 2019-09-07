<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics;

use FKSDB\Components\Controls\Fyziklani\FyziklaniReactControl;
use FKSDB\React\ReactResponse;
use FyziklaniModule\BasePresenter;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\ArgumentOutOfRangeException;
use Nette\Utils\DateTime;

/**
 * Class ResultsAndStatistics
 * @package FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics
 */
abstract class ResultsAndStatistics extends FyziklaniReactControl {

    /**
     * @return string
     */
    public final function getData(): string {
        return '';
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    public function getActions(): array {
        $actions = parent::getActions();
        $actions['refresh'] = $this->link('refresh!');
        return $actions;

    }

    /**
     * @throws AbortException
     */
    public function handleRefresh() {
        $presenter = $this->getPresenter();
        if (!($presenter instanceof BasePresenter)) {
            throw new ArgumentOutOfRangeException();
        }
        $isOrg = $presenter->getEventAuthorizator()->isAllowed('fyziklani.results', 'presentation', $this->getEvent());
        /**
         * @var \DateTime $lastUpdated
         */
        $request = $this->getReactRequest();
        $requestData = $request->requestData;
        $lastUpdated = $requestData ? $requestData : null;
        $response = new ReactResponse();
        $response->setAct('results-update');
        $gameSetup = $this->getEvent()->getFyziklaniGameSetup();
        $result = [
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'gameStart' => $gameSetup->game_start->format('c'),
            'gameEnd' => $gameSetup->game_end->format('c'),
            'times' => [
                'toStart' => strtotime($gameSetup->game_start) - time(),
                'toEnd' => strtotime($gameSetup->game_end) - time(),
                'visible' => $this->isResultsVisible(),
            ],
            'availablePoints' => $gameSetup->getAvailablePoints(),
            'lastUpdated' => (new DateTime())->format('c'),
            'isOrg' => $isOrg,
            'refreshDelay' => $gameSetup->refresh_delay,
            'submits' => [],
        ];

        if ($isOrg || $this->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->getSubmitsAsArray($this->getEvent(), $lastUpdated);
        }
        //if (!$lastUpdated) {
        $result['rooms'] = $this->getRooms();
        $result['teams'] = $this->serviceFyziklaniTeam->getTeamsAsArray($this->getEvent());
        $result['tasks'] = $this->serviceFyziklaniTask->getTasksAsArray($this->getEvent());
        $result['categories'] = ['A', 'B', 'C'];
        // }

        $response->setData($result);

        $this->getPresenter()->sendResponse($response);
    }

    /**
     * @return bool
     */
    private function isResultsVisible(): bool {
        $gameSetup = $this->getEvent()->getFyziklaniGameSetup();
        $hardDisplay = $gameSetup->result_hard_display;
        $before = (time() < strtotime($gameSetup->result_hide));
        $after = (time() > strtotime($gameSetup->result_display));

        return $hardDisplay || ($before && $after);
    }


}
