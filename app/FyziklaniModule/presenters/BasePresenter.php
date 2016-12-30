<?php


namespace FyziklaniModule;

use AuthenticatedPresenter;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\DI\Container;
use \Nette\Diagnostics\Debugger;
use \FKSDB\Components\Forms\Factories\FyziklaniFactory;
use Nette\Utils\Html;


abstract class BasePresenter extends AuthenticatedPresenter {
    /**
     *
     * @var \Nette\Database\Connection
     */
    public $database;
    public $event;
    /**
     * @var int
     * @persistent
     */
    public $eventID;

    public $eventYear;

    /**
     * @var FyziklaniFactory
     */
    public $fyziklaniFactory;
    /**
     *
     * @var Container
     */
    public $container;

    public function __construct(Connection $database, FyziklaniFactory $fyziklaniFactory, Container $container) {

        parent::__construct();
        $this->container = $container;
        $this->fyziklaniFactory = $fyziklaniFactory;
        $this->database = $database;
    }

    public function startup() {
        $this->event = $this->getCurrentEvent();
        Debugger::barDump($this->event);
        if (!$this->eventExist()) {
            throw new BadRequestException('Event nebyl nalezen.', 404);
        }
        if ($this->event->event_type_id != $this->container->parameters['fyziklani']['eventTypeID']) {
            throw new BadRequestException('Tento event není Fyzikláni.', 500);
        }
        $this->eventYear = $this->event->event_year;
        parent::startup();
    }

    /** Vrati true ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getCurrentEvent() ? true : false;
    }

    public function getTitle() {
        return Html::el()->add($this->title . Html::el('small')->add($this->eventYear ? ' | ' . $this->eventYear . '. FYKOSí Fyzikláni' : ''));
    }

    public function getCurrentEventID() {
        // $this->eventID = $this->eventID ?: 95;
        if (!$this->eventID) {
            $this->eventID = $this->database->table(\DbNames::TAB_EVENT)->where('event_type_id', 1)->max('event_id');
        }
        return $this->eventID;
    }

    /** vráti paramtre daného eventu
     * @TODO to ORM?
     */
    public function getCurrentEvent() {
        $eventID = $this->getCurrentEventID();
        return $this->database->table(\DbNames::TAB_EVENT)->where('event_id', $eventID)->fetch();

    }

    /**
     * @TODO to ORM?
     */
    protected function submitExist($taskID, $teamID) {
        return (bool)$this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('fyziklani_task_id=?', $taskID)->where('e_fyziklani_team_id=?', $teamID)->count();
    }

    /**
     * @TODO to ORM?
     */
    protected function getSubmit($submitID) {
        return $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('fyziklani_submit_id', $submitID)->fetch();
    }

    /**
     * @TODO to ORM?
     */
    public function submitToTeam($submitID) {
        $r = $this->getSubmit($submitID);
        return $r ? $r->e_fyziklani_team_id : $r;
    }

    /**
     * @TODO to ORM?
     */
    protected function isOpenSubmit($teamID) {
        $points = $this->database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->where('e_fyziklani_team_id', $teamID)->fetch()->points;
        return !is_numeric($points);
    }

    /**
     * @TODO to ORM?
     */
    protected function taskLabelToTaskID($taskLabel) {
        $row = $this->database->table(\DbNames::TAB_FYZIKLANI_TASK)->where('label = ?', $taskLabel)->where('event_id = ?', $this->eventID)->fetch();
        if ($row) {
            return $row->fyziklani_task_id;
        }
        return false;
    }

    /**
     * @TODO to ORM?
     */
    protected function teamExist($teamID) {
        return $this->database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->get($teamID)->event_id == $this->eventID;
    }
}
