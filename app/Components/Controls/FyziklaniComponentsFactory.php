<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\Components\Controls\Fyziklani\CloseControl;
use FKSDB\Components\Controls\Fyziklani\FinalResults;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results\ResultsPresentation;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results\ResultsView;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\CorrelationStatistics;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\TaskStatistics;
use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics\TeamStatistics;
use FKSDB\model\Fyziklani\TaskCodeHandlerFactory;
use FKSDB\ORM\ModelEvent;
use Nette\DI\Container;
use Nette\Localization\ITranslator;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceFyziklaniSubmit;

class FyziklaniComponentsFactory {

    /**
     * @var \ServiceFyziklaniRoom
     */
    private $serviceFyziklaniRoom;

    /**
     * @var \ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;

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
    /**
     * @var ITranslator
     */
    private $translator;

    public function __construct(
        \ServiceFyziklaniRoom $serviceFyziklaniRoom,
        \ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        \ServiceFyziklaniTask $serviceFyziklaniTask,
        \ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        TaskCodeHandlerFactory $taskCodeHandlerFactory,
        Container $context,
        ITranslator $translator
    ) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->serviceFyziklaniRoom = $serviceFyziklaniRoom;
        $this->taskCodeHandlerFactory = $taskCodeHandlerFactory;
        $this->context = $context;
        $this->translator = $translator;
    }

    public function createResultsView(ModelEvent $event): ResultsView {
        return new ResultsView($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createResultsPresentation(ModelEvent $event): ResultsPresentation {
        return new ResultsPresentation($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTeamStatistics(ModelEvent $event): TeamStatistics {
        return new TeamStatistics($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTaskStatistics(ModelEvent $event): TaskStatistics {
        return new TaskStatistics($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createRoutingEdit(ModelEvent $event): RoutingEdit {
        return new RoutingEdit($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createCorrelationStatistics(ModelEvent $event): CorrelationStatistics {
        return new CorrelationStatistics($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createTaskCodeInput(ModelEvent $event): TaskCodeInput {
        $handler = $this->taskCodeHandlerFactory->createHandler($event);
        return new TaskCodeInput($handler, $this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    public function createFinalResults(ModelEvent $event): FinalResults {
        return new FinalResults($event, $this->serviceFyziklaniTeam, $this->translator);
    }

    public function createCloseControl(ModelEvent $event): CloseControl {
        return new CloseControl($event, $this->serviceFyziklaniTeam, $this->translator);
    }

    public function createSubmitsGrid(ModelEvent $event): SubmitsGrid {
        return new SubmitsGrid($event, $this->serviceFyziklaniSubmit);
    }

}
