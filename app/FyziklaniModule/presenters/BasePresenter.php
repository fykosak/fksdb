<?php


namespace FyziklaniModule;

use AuthenticatedPresenter;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use \Nette\Diagnostics\Debugger;
use \FKSDB\Components\Forms\Factories\FyziklaniFactory;
use Nette\Utils\Html;
use ServiceEvent;
use ServiceFyziklaniTask;
use ServiceFyziklaniSubmit;
use \ORM\Services\Events\ServiceFyziklaniTeam;
use ModelEvent;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    /**
     *
     * @var ModelEvent
     */
    protected $event;
    /**
     * @var int $eventID
     * @persistent
     */
    public $eventID;
    /**
     * @var int $eventYear
     */
    public $eventYear;

    /**
     * @var FyziklaniFactory
     */
    protected $fyziklaniFactory;
    /**
     *
     * @var Container
     */
    protected $container;
    
    /**
     *
     * @var ServiceEvent
     */
    protected $serviceEvent;
    
    /**
     *
     * @var ServiceFyziklaniTeam
     */
    protected $serviceFyziklaniTeam;
    
    /**
     *
     * @var ServiceFyziklaniTask
     */
    protected $serviceFyziklaniTask;
    
    /**
     *
     * @var ServiceFyziklaniSubmit
     */
    protected $serviceFyziklaniSubmit;
    
    public function injectFyziklaniFactory(FyziklaniFactory $fyziklaniFactory) {
        $this->fyziklaniFactory = $fyziklaniFactory;
    }
    
    public function injectContainer(Container $container) {
        $this->container = $container;
    }
    
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }
    
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }
    
    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }
    
    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    public function startup() {
        $this->event = $this->getCurrentEvent();
        if (!$this->eventExist()) {
            throw new BadRequestException('Event nebyl nalezen.', 404);
        }
        $this->eventYear = $this->event->event_year;
        parent::startup();
    }

    /** Vrati true ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getCurrentEvent() ? true : false;
    }

    public function getTitle() {
        return Html::el()->add($this->title . Html::el('small')->add($this->eventYear ? (' '.$this->eventYear . '. FYKOSí Fyzikláni') : ''));
    }

    public function getCurrentEventID() {
        if (!$this->eventID) {
            $this->eventID = $this->serviceEvent->getTable()
                ->where('event_type_id', $this->container->parameters['fyziklani']['eventTypeID'])
                ->max('event_id');
        }
        return $this->eventID;
    }

    /** vráti paramtre daného eventu 
     * @return ModelEvent
     */
    public function getCurrentEvent() {
        if(!$this->event){
            $this->event = $this->serviceEvent->findByPrimary($this->getCurrentEventID());
        }
        return $this->event;
    }

    protected function submitExist($taskID, $teamID) {
        return !is_null($this->serviceFyziklaniSubmit->findByTaskAndTeam($taskID, $teamID));
    }

    protected function getSubmit($submitID) {
        return $this->serviceFyziklaniSubmit->findByPrimary($submitID);
    }

    public function submitToTeam($submitID) {
        $r = $this->getSubmit($submitID);
        return $r ? $r->e_fyziklani_team_id : $r;
    }

    public function isOpenSubmit($teamID) {
        $points = $this->serviceFyziklaniTeam->findByPrimary($teamID)->points;
        return !is_numeric($points);
    }

    protected function taskLabelToTaskID($taskLabel) {
        $row = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->eventID);
        if ($row) {
            return $row->fyziklani_task_id;
        }
        return false;
    }

    protected function teamExist($teamID) {
        return $this->serviceFyziklaniTeam->findByPrimary($teamID)->event_id == $this->eventID;
    }
}
