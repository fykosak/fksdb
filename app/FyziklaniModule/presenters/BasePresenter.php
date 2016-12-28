<?php


namespace FyziklaniModule;

use Authorization\Assertions\EventOrgByIdAssertion;
use FKSDB\model\Fyziklani\FyziklaniTaskImportProcessor;
use AuthenticatedPresenter;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use \Nette\Application\UI\Form;
use Nette\Database\Connection;
use Nette\DI\Container;
use \Nette\Diagnostics\Debugger;
use \FKSDB\Components\Forms\Factories\FyziklaniFactory;
use \FKSDB\Components\Grids\Fyziklani\FyziklaniTaskGrid;

abstract class BasePresenter extends AuthenticatedPresenter {

    const EVENT_TYPE_ID = 1;
    const IMPORT_STATE_UPDATE_N_INSERT = 1;
    const IMPORT_STATE_REMOVE_N_INSERT = 2;
    const IMPORT_STATE_INSERT = 3;

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
private $EventOrgByIdAssertion;
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
        parent::startup();
    }

    public function titleDefault() {
        $this->setTitle(_('Fykosí Fyzikláni'));
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->eventAuth->isAllowed('fyziklani','results',$this->eventID));
       // $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'default', $this->getSelectedContest()));
    }



    public function titleTask() {
        $this->setTitle(_('Úlohy Fykosího Fyzikláni'));
    }

    public function authorizedTask() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'task', $this->getSelectedContest()));
    }

    public function titleTaskimport() {
        $this->setTitle(_('Import úloh Fykosího Fyzikláni'));
    }

    public function authorizedTaskimport() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'taskimport', $this->getSelectedContest()));
    }





    /** Vrati true ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getCurrentEvent() ? true : false;
    }



    public function getCurrentEventID() {
        return $this->getCurrentEvent()->event_id;
    }

    /** vráti paramtre daného eventu */
    public function getCurrentEvent() {
        return $this->database->table(\DbNames::TAB_EVENT)->where('event_id', $this->eventID)->fetch();
    }



    public function createComponentTaskImportForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer);
        $form->addUpload('csvfile')->setRequired();
        $form->addSelect('state', _('Vyberte akciu'), [self::IMPORT_STATE_UPDATE_N_INSERT => _('Updatnuť úlohy a pridať ak neexistuje'), self::IMPORT_STATE_REMOVE_N_INSERT => _('Ostrániť všetky úlohy a nahrať nove'), self::IMPORT_STATE_INSERT => _('Pridať ak neexistuje')]);
        $form->addSubmit('import', _('importovať'));
        $form->onSuccess[] = [$this, 'taskImportFormSucceeded'];
        return $form;
    }

    public function taskImportFormSucceeded(Form $form) {
        $values = $form->getValues();
        $taskImportProcessor = new FyziklaniTaskImportProcessor($this);
        $taskImportProcessor->preprosess($values);
        $this->redirect('this');
    }

    public function createComponentTaskGrid() {
        return new FyziklaniTaskGrid($this);
    }


}