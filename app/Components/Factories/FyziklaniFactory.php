<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;
use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\Controls\Fyziklani\RoutingEdit;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\model\Fyziklani\SubmitHandler;
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
     * @var Container
     */
    private $context;
    /**
     * @var ITranslator
     */
    private $translator;
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
        Container $context,
        ITranslator $translator,
        TableReflectionFactory $tableReflectionFactory
    ) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->serviceFyziklaniRoom = $serviceFyziklaniRoom;
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->context = $context;
        $this->translator = $translator;
    }

    /* ********** ENTRY FORMS + EDIT **********/
    /**
     * @param ModelEvent $event
     * @return TaskCodeInput
     */
    public function createTaskCodeInput(ModelEvent $event): TaskCodeInput {
        return new TaskCodeInput($this->createHandler($event), $this->context, $event, $this->serviceFyziklaniTask, $this->serviceFyziklaniTeam);
    }

    /**
     * @param ModelEvent $event
     * @return SubmitHandler
     */
    private function createHandler(ModelEvent $event): SubmitHandler {
        return new SubmitHandler(
            $this->serviceFyziklaniTeam,
            $this->serviceFyziklaniTask,
            $this->serviceFyziklaniSubmit,
            $event
        );
    }

    /* ************** ROUTING *************/

    /**
     * @param ModelEvent $event
     * @return RoutingEdit
     */
    public function createRoutingEdit(ModelEvent $event): RoutingEdit {
        return new RoutingEdit($this->context, $event, $this->serviceFyziklaniRoom, $this->serviceFyziklaniTeamPosition, $this->serviceFyziklaniTeam);
    }

    /* *********** RESULTS & STATS ********/

    /**
     * @param string $reactId
     * @param ModelEvent $event
     * @return ResultsAndStatistics
     */
    public function createResultsAndStatistics(string $reactId, ModelEvent $event) {
        return new ResultsAndStatistics($reactId, $this->context, $event);
    }
    /* ********** GRIDS *************/
    /**
     * @param ModelEvent $event
     * @return RoutingDownload
     */
    public function createRoutingDownload(ModelEvent $event): RoutingDownload {
        return new RoutingDownload($event, $this->translator, $this->serviceFyziklaniTeam, $this->serviceFyziklaniRoom);
    }
}
