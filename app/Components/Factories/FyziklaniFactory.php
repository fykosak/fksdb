<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Controls\Fyziklani\CloseControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamControl;
use FKSDB\Components\Controls\Fyziklani\EditSubmitControl;
use FKSDB\Components\Controls\Fyziklani\FinalResults;
use FKSDB\Components\Controls\Fyziklani\QREntryControl;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results\ResultsPresentation;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results\ResultsView;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\CorrelationStatistics;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\TaskStatistics;
use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics\TeamStatistics;
use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\Controls\Fyziklani\RoutingEdit;
use FKSDB\Components\Controls\Fyziklani\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\AllSubmitsGrid;
use FKSDB\Components\Grids\Fyziklani\TaskGrid;
use FKSDB\Components\Grids\Fyziklani\TeamSubmitsGrid;
use FKSDB\model\Fyziklani\TaskCodeHandlerFactory;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\DI\Container;
use Nette\Localization\ITranslator;
use ORM\Models\Events\ModelFyziklaniTeam;
use ORM\Services\Events\ServiceFyziklaniTeam;

/**
 * Class FyziklaniFactory
 * @package FKSDB\Components\Factories
 */
class FyziklaniFactory {

    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom
     */
    private $serviceFyziklaniRoom;

    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask
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
     * FyziklaniFactory constructor.
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom $serviceFyziklaniRoom
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param TaskCodeHandlerFactory $taskCodeHandlerFactory
     * @param Container $context
     * @param ITranslator $translator
     */
    public function __construct(
        \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom $serviceFyziklaniRoom,
        \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask,
        \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
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
     * @return EditSubmitControl
     */
    public function createEditSubmitControl(ModelEvent $event): EditSubmitControl {
        return new EditSubmitControl($event, $this->serviceFyziklaniSubmit, $this->translator);
    }

    /* *************** CLOSING ***************/

    /**
     * @param ModelEvent $event
     * @return CloseControl
     */
    public function createCloseControl(ModelEvent $event): CloseControl {
        return new CloseControl($event, $this->serviceFyziklaniTeam, $this->translator);
    }

    /**
     * @param ModelEvent $event
     * @return CloseTeamControl
     */
    public function createCloseTeamControl(ModelEvent $event): CloseTeamControl {
        return new CloseTeamControl($event, $this->serviceFyziklaniTeam, $this->translator, $this->serviceFyziklaniTask, $this);
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
        return new FinalResults($event, $this->serviceFyziklaniTeam, $this->translator);
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
        return new AllSubmitsGrid($event, $this->serviceFyziklaniSubmit);
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
        return new TeamSubmitsGrid($team, $this->serviceFyziklaniSubmit);
    }
}
