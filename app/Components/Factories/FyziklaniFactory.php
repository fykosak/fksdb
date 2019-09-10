<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Controls\Fyziklani\CloseTeamControl;
use FKSDB\Components\Controls\Fyziklani\EditControl;
use FKSDB\Components\Controls\Fyziklani\FinalResults;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results\ResultsPresentation;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results\ResultsView;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\CorrelationStatistics;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\TaskStatistics;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\TeamStatistics;
use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\Controls\Fyziklani\RoutingEdit;
use FKSDB\Components\Controls\Fyziklani\Submit\DetailControl;
use FKSDB\Components\Controls\Fyziklani\Submit\QREntryControl;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Forms\Factories\Fyziklani\CloseFormsFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\Fyziklani\AllSubmitsGrid;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\Components\Grids\Fyziklani\ResultsCategoryGrid;
use FKSDB\Components\Grids\Fyziklani\ResultsTotalGrid;
use FKSDB\Components\Grids\Fyziklani\TaskGrid;
use FKSDB\Components\Grids\Fyziklani\TeamSubmitsGrid;
use FKSDB\model\Fyziklani\TaskCodeHandlerFactory;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

/**
 * Class FyziklaniFactory
 * @package FKSDB\Components\Factories
 */
class FyziklaniFactory {

    /**
     * @var ServiceFyziklaniRoom
     */
    private $serviceFyziklaniRoom;

    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;

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
    /**
     * @var CloseFormsFactory
     */
    private $closeFormsFactory;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * FyziklaniFactory constructor.
     * @param ServiceFyziklaniRoom $serviceFyziklaniRoom
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param TaskCodeHandlerFactory $taskCodeHandlerFactory
     * @param Container $context
     * @param ITranslator $translator
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(
        ServiceFyziklaniRoom $serviceFyziklaniRoom,
        ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        ServiceFyziklaniTask $serviceFyziklaniTask,
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        TaskCodeHandlerFactory $taskCodeHandlerFactory,
        Container $context,
        ITranslator $translator,
        TableReflectionFactory $tableReflectionFactory
    ) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->serviceFyziklaniRoom = $serviceFyziklaniRoom;
        $this->taskCodeHandlerFactory = $taskCodeHandlerFactory;
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->context = $context;
        $this->translator = $translator;
        $this->closeFormsFactory = new CloseFormsFactory($serviceFyziklaniTeam);
    }

    /**
     * @return CloseFormsFactory
     */
    public function getCloseFormsFactory(): CloseFormsFactory {
        return $this->closeFormsFactory;
    }

    /* ********** ENTRY FORMS + EDIT **********/
    /**
     * @param ModelEvent $event
     * @return TaskCodeInput
     */
    public function createTaskCodeInput(ModelEvent $event): TaskCodeInput {
        $handler = $this->taskCodeHandlerFactory->createHandler($event);
        return new TaskCodeInput($handler, $this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    /**
     * @param ModelEvent $event
     * @return QREntryControl
     */
    public function createQREntryControl(ModelEvent $event): QREntryControl {
        $handler = $this->taskCodeHandlerFactory->createHandler($event);
        return new QREntryControl($event, $handler, $this->translator);
    }

    /**
     * @param ModelEvent $event
     * @return EditControl
     */
    public function createEditSubmitControl(ModelEvent $event): EditControl {
        return new EditControl($event, $this->serviceFyziklaniSubmit, $this->translator);
    }

    /* *************** CLOSING ***************/

    /**
     * @param ModelEvent $event
     * @return CloseTeamControl
     */
    public function createCloseTeamControl(ModelEvent $event): CloseTeamControl {
        return new CloseTeamControl($event, $this->serviceFyziklaniTeam, $this->translator, $this->serviceFyziklaniTask, $this);
    }

    /**
     * @param ModelEvent $event
     * @return CloseTeamsGrid
     */
    public function createCloseTeamsGrid(ModelEvent $event): CloseTeamsGrid {
        return new CloseTeamsGrid($event, $this->serviceFyziklaniTeam, $this->tableReflectionFactory);
    }

    /* ************** ROUTING *************/

    /**
     * @param ModelEvent $event
     * @return RoutingEdit
     */
    public function createRoutingEdit(ModelEvent $event): RoutingEdit {
        return new RoutingEdit($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    /* *********** RESULTS & STATS ********/
    /**
     * @param ModelEvent $event
     * @return FinalResults
     */
    public function createFinalResults(ModelEvent $event): FinalResults {
        return new FinalResults($event, $this->serviceFyziklaniTeam, $this->translator, $this->tableReflectionFactory);
    }

    /**
     * @param ModelEvent $event
     * @param string $category
     * @return ResultsCategoryGrid
     */
    public function createResultsCategoryGrid(ModelEvent $event, string $category): ResultsCategoryGrid {
        return new ResultsCategoryGrid($event, $this->serviceFyziklaniTeam, $category, $this->tableReflectionFactory);
    }

    /**
     * @param ModelEvent $event
     * @return ResultsTotalGrid
     */
    public function createResultsTotalGrid(ModelEvent $event): ResultsTotalGrid {
        return new ResultsTotalGrid($event, $this->serviceFyziklaniTeam, $this->tableReflectionFactory);
    }

    /**
     * @param ModelEvent $event
     * @return ResultsView
     */
    public function createResultsView(ModelEvent $event): ResultsView {
        return new ResultsView($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    /**
     * @param ModelEvent $event
     * @return ResultsPresentation
     */
    public function createResultsPresentation(ModelEvent $event): ResultsPresentation {
        return new ResultsPresentation($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    /**
     * @param ModelEvent $event
     * @return TeamStatistics
     */
    public function createTeamStatistics(ModelEvent $event): TeamStatistics {
        return new TeamStatistics($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    /**
     * @param ModelEvent $event
     * @return TaskStatistics
     */
    public function createTaskStatistics(ModelEvent $event): TaskStatistics {
        return new TaskStatistics($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    /**
     * @param ModelEvent $event
     * @return CorrelationStatistics
     */
    public function createCorrelationStatistics(ModelEvent $event): CorrelationStatistics {
        return new CorrelationStatistics($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit);
    }

    /* ********** GRIDS *************/
    /**
     * @param ModelEvent $event
     * @return AllSubmitsGrid
     */
    public function createSubmitsGrid(ModelEvent $event): AllSubmitsGrid {
        return new AllSubmitsGrid(
            $event,
            $this->serviceFyziklaniTask,
            $this->serviceFyziklaniSubmit,
            $this->serviceFyziklaniTeam,
            $this->tableReflectionFactory
        );
    }

    /**
     * @param ModelEvent $event
     * @return TaskGrid
     */
    public function createTasksGrid(ModelEvent $event): TaskGrid {
        return new TaskGrid($event, $this->serviceFyziklaniTask);
    }

    /**
     * @param ModelEvent $event
     * @return RoutingDownload
     */
    public function createRoutingDownload(ModelEvent $event): RoutingDownload {
        return new RoutingDownload($event, $this->translator, $this->serviceFyziklaniTeam, $this->serviceFyziklaniRoom);
    }

    /**
     * @param ModelFyziklaniTeam $team
     * @return TeamSubmitsGrid
     */
    public function createTeamSubmitsGrid(ModelFyziklaniTeam $team): TeamSubmitsGrid {
        return new TeamSubmitsGrid($team, $this->serviceFyziklaniSubmit, $this->tableReflectionFactory);
    }

    /**
     * @return DetailControl
     */
    public function createSubmitDetailControl(): DetailControl {
        return new DetailControl($this->translator, $this->serviceFyziklaniSubmit);
    }
}
