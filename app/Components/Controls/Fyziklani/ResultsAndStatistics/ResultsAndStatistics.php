<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics;

use FKSDB\Components\Controls\Fyziklani\FyziklaniReactControl;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\React\ReactResponse;
use FKSDB\Modules\FyziklaniModule\BasePresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\ArgumentOutOfRangeException;
use Nette\Http\Response;
use Nette\Utils\DateTime;

/**
 * Class ResultsAndStatistics
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ResultsAndStatistics extends FyziklaniReactControl {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    /**
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @return void
     */
    public function injectPrimary(
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        ServiceFyziklaniTask $serviceFyziklaniTask,
        ServiceFyziklaniTeam $serviceFyziklaniTeam
    ) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    final public function getData(...$args): string {
        return '';
    }

    /**
     * @return void
     * @throws InvalidLinkException
     */
    protected function configure() {
        $this->addAction('refresh', $this->link('refresh!'));
        parent::configure();
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws NotSetGameParametersException
     */
    public function handleRefresh() {
        $presenter = $this->getPresenter();
        if (!$presenter->isAjax()) {
            throw new BadRequestException('', Response::S405_METHOD_NOT_ALLOWED);
        }
        if (!$presenter instanceof BasePresenter) {
            throw new ArgumentOutOfRangeException();
        }
        $isOrg = $presenter->getEventAuthorizator()->isContestOrgAllowed('fyziklani.results', 'presentation', $this->getEvent());

        $request = $this->getReactRequest();

        $lastUpdated = $request->requestData ?: null;
        $response = new ReactResponse();
        $response->setAct('results-update');
        $gameSetup = $this->getEvent()->getFyziklaniGameSetup();
        $result = [
            'availablePoints' => $gameSetup->getAvailablePoints(),
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'gameStart' => $gameSetup->game_start->format('c'),
            'gameEnd' => $gameSetup->game_end->format('c'),
            'times' => [
                'toStart' => strtotime($gameSetup->game_start) - time(),
                'toEnd' => strtotime($gameSetup->game_end) - time(),
                'visible' => $this->isResultsVisible(),
            ],

            'lastUpdated' => (new DateTime())->format('c'),
            'isOrg' => $isOrg,
            'refreshDelay' => $gameSetup->refresh_delay,
            'tasksOnBoard' => $gameSetup->tasks_on_board,
            'submits' => [],
        ];

        if ($isOrg || $this->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->getSubmitsAsArray($this->getEvent(), $lastUpdated);
        }
        // probably need refresh before competition started
        if (!$lastUpdated) {
            $result['teams'] = $this->serviceFyziklaniTeam->getTeamsAsArray($this->getEvent());
            $result['tasks'] = $this->serviceFyziklaniTask->getTasksAsArray($this->getEvent());
            $result['categories'] = ['A', 'B', 'C'];
        }
        $response->setData($result);
        $this->getPresenter()->sendResponse($response);
    }

    /**
     * @return bool
     * @throws NotSetGameParametersException
     */
    private function isResultsVisible(): bool {
        return $this->getEvent()->getFyziklaniGameSetup()->isResultsVisible();
    }
}
