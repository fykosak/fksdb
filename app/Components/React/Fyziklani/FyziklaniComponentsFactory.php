<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results\ResultsPresentation;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results\ResultsView;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\CorrelationStatistics;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\TaskStatistics;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\TeamStatistics;
use FKSDB\model\Fyziklani\TaskCodeHandlerFactory;
use FKSDB\ORM\ModelEvent;
use Nette\DI\Container;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceFyziklaniSubmit;

class FyziklaniComponentsFactory {

    /**
     * @var \ServiceBrawlRoom
     */
    private $serviceBrawlRoom;

    /**
     * @var \ServiceBrawlTeamPosition
     */
    private $serviceBrawlTeamPosition;

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var \ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var TaskCodeHandlerFactory
     */
    private $taskCodeHandlerFactory;
    /**
     * @var Container
     */
    private $context;

    public function __construct(
        \ServiceBrawlRoom $serviceBrawlRoom,
        \ServiceBrawlTeamPosition $serviceBrawlTeamPosition,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        \ServiceFyziklaniTask $serviceFyziklaniTask,
        \ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        TaskCodeHandlerFactory $taskCodeHandlerFactory,
        Container $context
    ) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
        $this->serviceBrawlRoom = $serviceBrawlRoom;
        $this->taskCodeHandlerFactory = $taskCodeHandlerFactory;
        $this->context = $context;
    }

    public function createResultsView(ModelEvent $event) {
        return new ResultsView($this->context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createResultsPresentation(ModelEvent $event) {
        return new ResultsPresentation($this->context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTeamStatistics(ModelEvent $event) {
        return new TeamStatistics($this->context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTaskStatistics(ModelEvent $event) {
        return new TaskStatistics($this->context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createRoutingEdit(ModelEvent $event) {
        return new RoutingEdit($this->context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createCorrelationStatistics(ModelEvent $event) {
        return new CorrelationStatistics($this->context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTaskCodeInput(ModelEvent $event) {
        $handler = $this->taskCodeHandlerFactory->createHandler($event);
        return new TaskCodeInput($handler, $this->context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }
}
