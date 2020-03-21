<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics;

use FKSDB\Components\Controls\Fyziklani\FyziklaniReactControl;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\React\ReactResponse;
use FyziklaniModule\BasePresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\ArgumentOutOfRangeException;
use Nette\DI\Container;
use Nette\Utils\DateTime;

/**
 * Class ResultsAndStatistics
 * @package FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics
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
     * @var string
     */
    private $reactId;

    /**
     * ResultsAndStatistics constructor.
     * @param string $reactId
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(string $reactId, Container $container, ModelEvent $event) {
        parent::__construct($container, $event);
        $this->reactId = $reactId;
        $this->serviceFyziklaniSubmit = $this->container->getByType(ServiceFyziklaniSubmit::class);
        $this->serviceFyziklaniTask = $this->container->getByType(ServiceFyziklaniTask::class);
        $this->serviceFyziklaniTeam = $this->container->getByType(ServiceFyziklaniTeam::class);
    }

    /**
     * @return string
     */
    protected function getReactId(): string {
        return $this->reactId;
    }

    /**
     * @return string
     */
    public final function getData(): string {
        return '';
    }

    /**
     * @throws InvalidLinkException
     */
    protected function configure() {
        $this->addAction('refresh', $this->link('refresh!'));
        parent::configure();
    }

    /**
     * @throws AbortException
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    public function handleRefresh() {
        $presenter = $this->getPresenter();
        if (!$presenter->isAjax()) {
            throw new ForbiddenRequestException();
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
