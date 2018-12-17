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

    public function __construct(
        \ServiceBrawlRoom $serviceBrawlRoom,
        \ServiceBrawlTeamPosition $serviceBrawlTeamPosition,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        \ServiceFyziklaniTask $serviceFyziklaniTask,
        \ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        TaskCodeHandlerFactory $taskCodeHandlerFactory
    ) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
        $this->serviceBrawlRoom = $serviceBrawlRoom;
        $this->taskCodeHandlerFactory = $taskCodeHandlerFactory;
    }

    public function createResultsView(Container $context, ModelEvent $event) {
        return new ResultsView($context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createResultsPresentation(Container $context, ModelEvent $event) {
        return new ResultsPresentation($context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTeamStatistics(Container $context, ModelEvent $event) {
        return new TeamStatistics($context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTaskStatistics(Container $context, ModelEvent $event) {
        return new TaskStatistics($context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createRoutingEdit(Container $context, ModelEvent $event) {
        return new RoutingEdit($context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createCorrelationStatistics(Container $context, ModelEvent $event) {
        return new CorrelationStatistics($context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTaskCodeInput(Container $context, ModelEvent $event) {
        $handler = $this->taskCodeHandlerFactory->createHandler($event);
        return new TaskCodeInput($handler, $context, $event, $this->serviceBrawlRoom, $this->serviceBrawlTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }
}
