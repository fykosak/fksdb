<?php


namespace FyziklaniModule;

use AuthenticatedPresenter;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\DI\Container;
use \Nette\Diagnostics\Debugger;
use \FKSDB\Components\Forms\Factories\FyziklaniFactory;


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

    public function __construct(Connection $database, FyziklaniFactory $pointsFactory, Container $container) {

        parent::__construct();
        $this->container = $container;
        $this->fyziklaniFactory = $pointsFactory;
        $this->database = $database;
    }

    public function startup() {
        //$this->eventID = $this->params['eventID'];
        $this->event = $this->getCurrentEvent();
        Debugger::barDump($this->event);
        if (!$this->eventExist()) {
            throw new BadRequestException('Pre tento ročník nebolo najduté Fyzikláni', 404);
        }
        if ($this->event->event_type_id != $this->container->parameters['fyziklani']['eventTypeID']) {
            throw new BadRequestException('Tento event nieje Fyzikláni', 500);
        }
        $this->eventYear = $this->event->event_year;
        // $this->flashMessage(_('Náchádzate sa v ' . $this->eventYear . '. Fykosím Fyzikláni'), 'warning');
        parent::startup();

    }

    /** Vrati true ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getCurrentEvent() ? true : false;
    }

    public function getTitle() {
        return $this->title . ($this->eventYear ? ' | ' . $this->eventYear . '. FYKOSí Fyzikláni' : '');
    }

    public function getCurrentEventID() {
        return $this->getCurrentEvent()->event_id;
    }

    /** vráti paramtre daného eventu */
    public function getCurrentEvent() {
        // $this->eventID = $this->eventID ?: 95;
        if (!$this->eventID) {
            $this->eventID = $this->database->table(\DbNames::TAB_EVENT)->where('event_type_id', 1)->max('event_id');
        }
        return $this->database->table(\DbNames::TAB_EVENT)->where('event_id', $this->eventID)->fetch();


    }
}
