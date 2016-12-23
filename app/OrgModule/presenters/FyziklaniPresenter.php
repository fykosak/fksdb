<?php

namespace OrgModule;

use FKSDB\model\Fyziklani\FyziklaniTaskImportProcessor;
use Fyziklani\CloseSubmitStragegy;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use \Nette\Application\UI\Form;
use Nette\Database\Connection;
use Nette\DI\Container;
use \Nette\Diagnostics\Debugger;
use \FKSDB\Components\Forms\Factories\FyziklaniFactory;
use \FKSDB\Components\Grids\Fyziklani\FyziklaniTeamsGrid;
use \FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid;
use \FKSDB\Components\Grids\Fyziklani\FyziklaniTaskGrid;
use FKSDB\model\Fyziklani\TaskCodePreprocessor;

class FyziklaniPresenter extends BasePresenter {

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
    public $eventID;
    public $eventYear;

    /**
     * @var FyziklaniFactory
     */
    private $fyziklaniFactory;
    /**
     * @var TaskCodePreprocessor
     */
    private $taskCodePreprocessor;
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
        $this->taskCodePreprocessor = new TaskCodePreprocessor();
    }

    public function startup() {
        if (!$this->eventExist()) {
            throw new BadRequestException('Pre tento ročník nebolo najduté Fyzikláni', 404);
        }
        $this->event = $this->getCurrentEvent();
        $this->eventYear = $this->event->event_year;
        $this->eventID = $this->getCurrentEventID();
        parent::startup();
    }

    public function renderResults() {
        if ($this->isAjax()) {
            $result = [];
            $type = $this->getHttpRequest()->getQuery('type');

            if ($type == 'init') {
                foreach ($this->database->table(\DbNames::TAB_FYZIKLANI_TASK)->where('event_id', $this->eventID)->order('label') as $row) {
                    $result['tasks'][] = ['label' => $row->label, 'name' => $row->name, 'task_id' => $row->fyziklani_task_id];
                }
                foreach ($this->database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->where('event_id', $this->eventID) as $row) {
                    $result['teams'][] = ['category' => $row->category, 'room' => $row->room, 'name' => $row->name, 'team_id' => $row->e_fyziklani_team_id];
                }
            } elseif ($type == 'refresh') {
                $submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team.event_id', $this->eventID);
                foreach ($submits as $submit) {
                    $result['submits'][] = ['points' => $submit->points, 'team_id' => $submit->e_fyziklani_team_id, 'task_id' => $submit->fyziklani_task_id];
                }
            } else {
                throw new BadRequestException('error', 404);
            }

            $result['times'] = ['toStart' => strtotime($this->container->parameters['fyziklani']['start']) - time(), 'toEnd' => strtotime($this->container->parameters['fyziklani']['end']) - time(), 'visible' => $this->isResultsVisible()];

            $this->sendResponse(new JsonResponse($result));
        } else {

        }
    }

    private function isResultsVisible() {
        return (time() < strtotime($this->container->parameters['fyziklani']['results']['hidde'])) && (time() > strtotime($this->container->parameters['fyziklani']['results']['display']));
    }

    public function renderClose($id) {
        $this->template->id = $id;
        if ($id) {
            $this->template->submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id', $id);
        }
    }

    public function actionEntry($id) {

        if ($id) {
            if ($this->checkTaskCode($id, $msg)) {
                $this['entryForm']->setDefaults(['taskCode' => $id]);
            } else {
                $this->flashMessage($msg, 'danger');

                $this->redirect(':Org:Fyziklani:entry');
            }
        }
    }

//    protected function createComponentEntryByTaksCodeForm() {
//        $form = new Form();
//        $form->addText('taskCode',_('Kód úlohy'));
//        //   $form->addHidden('points');
//        $form->setRenderer(new BootstrapRenderer);
//
//        foreach ($this->container->parameters['fyziklani']['availablePionts'] as $v) {
//            $form->addSubmit('points'.$v,_($v.' bobů'));
//        }
//        $form->onSuccess[] = [$this,'entryFormByTaksCodeSucceeded'];
//
//        return $form;
//    }

    public function createComponentSubmitsGrid() {
        $grid = new FyziklaniSubmitsGrid($this);
        return $grid;
    }

    public function createComponentCloseGrid() {
        $grid = new FyziklaniTeamsGrid($this->database);
        return $grid;
    }

    public function createComponentCloseForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('e_fyziklani_team_id', 0);
        $form->addCheckbox('submit_task_correct', _('Úlohy a počty bodov sú správne'))->setRequired(_('Skontrolujte prosím správnosť zadania bodov!'));
        $form->addText('next_task', _('Úloha u vydávačov'))->setDisabled();
        $form->addCheckbox('next_task_correct', _('Úloha u vydávačov sa zhaduje'))->setRequired(_('Skontrolujte prosím zhodnosť úlohy u vydávačov'));


        $form->addSubmit('send', 'Potvrdiť spravnosť');
        $form->onSuccess[] = [$this, 'closeFormSucceeded'];
        return $form;
    }

    public function closeFormSucceeded(Form $form) {

        $values = $form->getValues();
        $submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id', $values->e_fyziklani_team_id);
        $sum = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
        }
        if ($this->database->query('UPDATE ' . \DbNames::TAB_E_FYZIKLANI_TEAM . ' SET ? WHERE e_fyziklani_team_id=? ', ['points' => $sum], $values->e_fyziklani_team_id)) {
            $this->redirect(':Org:Fyziklani:close');
        }
    }

    public function createComponentCloseCategoryForm($category) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('category', $category);
        $form->addSubmit('send', _('Uzavrieť kategoriu' . $category));
        $form->onSuccess[] = [$this, 'closeCategoryFormSucceeded'];
        return $form;
    }

    public function createComponentCloseCategoryAForm() {
        return $this->createComponentCloseCategoryForm('A');
    }

    public function createComponentCloseCategoryBForm() {
        return $this->createComponentCloseCategoryForm('B');
    }

    public function createComponentCloseCategoryCForm() {
        return $this->createComponentCloseCategoryForm('C');
    }

    public function closeCategoryFormSucceeded(Form $form) {
        $closeStrategy = new CloseSubmitStragegy($this);
        $closeStrategy->closeByCategory($form->getValues()->category);
    }

    public function createComponentCloseGlobalForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addSubmit('send', _('Uzavrieť Fyzikláni'));
        $form->onSuccess[] = [$this, 'closeGlobalFormSucceeded'];
        return $form;
    }

    public function closeGlobalFormSucceeded() {
        $closeStrategy = new CloseSubmitStragegy($this);
        $closeStrategy->closeGlobal();
    }


    private function isReadyToClose($category = null) {
        $database = $this->database;
        $query = $database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->where('status', 'participated')->where('event_id', $this->eventID);
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;

    }

    /**
     * @TODO ak je už uzavreté vyfakovať
     * @param integer $id
     */
    public function actionClose($id) {

        if ($id) {
            if ($this->isOpenSubmit($id)) {
                if (!$this->teamExist($id)) {
                    $this->flashMessage('Tým neexistuje', 'danger');
                    $this->redirect(':org:fyziklani:close');
                }
                $this['closeForm']->setDefaults(['e_fyziklani_team_id' => $id, 'next_task' => $this->getNextTask($id)->nextTask]);
            } else {
                $this->flashMessage('tento tým má už uzavreté bodovanie', 'danger');
                $this->redirect(':org:fyziklani:close');
            }
        }

        if (!$this->isReadyToClose('A')) {
            $this['closeCategoryAForm']['send']->setDisabled();
        }
        if (!$this->isReadyToClose('B')) {
            $this['closeCategoryBForm']['send']->setDisabled();
        }
        if (!$this->isReadyToClose('C')) {
            $this['closeCategoryCForm']['send']->setDisabled();
        }
        if (!$this->isReadyToClose()) {
            $this['closeGlobalForm']['send']->setDisabled();
        }
    }

    public function getNextTask($teamID) {

        $return = [];
        $submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id=?', $teamID)->count();
        $allTask = $this->database->query('SELECT * FROM ' . \DbNames::TAB_FYZIKLANI_TASK . ' WHERE event_id = ? ORDER BY label', $this->eventID)->fetchAll();
        $lastID = $submits + $this->container->parameters['fyziklani']['taskOnBoard'] - 1;
        /** @because index start with 0; */
        $nextID = $lastID + 1;
        if (array_key_exists($nextID, $allTask)) {
            $return['nextTask'] = $allTask[$nextID]->label;
        } else {
            $return['nextTask'] = null;
        }
        if (array_key_exists($lastID, $allTask)) {
            $return['lastTask'] = $allTask[$lastID]->label;
        } else {
            $return['lastTask'] = null;
        }
        return (object)$return;
    }

    public function createComponentFyziklaniEditForm() {
        $form = $this->fyziklaniFactory->createEditForm();
        $form->setRenderer(new BootstrapRenderer());

        $form->addSubmit('send', 'Uložit');
        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function actionEdit($id) {
        if (!$id) {
            throw new BadRequestException('ID je povinné', 400);
        }
        /* Neexitujúci submit nejde editovať */
        $teamID = $this->submitToTeam($id);
        if (!$teamID) {
            $this->flashMessage(_('Submit neexistuje'), 'danger');
            $this->redirect(':Org:Fyziklani:submits');
        }
        /* Uzatvorené bodovanie nejde editovať; */
        if (!$this->isOpenSubmit($teamID)) {
            $this->flashMessage(_('Bodovaní tohto týmu je uzvřené'), 'danger');
            $this->redirect(':Org:Fyziklani:submits');
        }
        $submit = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name')->where('fyziklani_submit_id = ?', $id)->fetch();
        $this->template->fyziklani_submit_id = $submit ? true : false;
        $this['fyziklaniEditForm']->setDefaults(['team_id' => $submit->e_fyziklani_team_id, 'task' => $submit->label, 'points' => $submit->points, 'team' => $submit->name, 'submit_id' => $submit->fyziklani_submit_id]);
    }

    public function createComponentEntryForm() {
        $form = $this->fyziklaniFactory->createEntryForm();
        $form->setRenderer(new BootstrapRenderer());
        $form->onSuccess[] = [$this, 'entryFormSucceeded'];
        return $form;
    }

    public function titleEntry() {
        $this->setTitle(_('Zadávaní bodů'));
    }

    public function authorizedEntry() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'entry', $this->getSelectedContest()));
    }

    public function titleEdit() {
        $this->setTitle(_('Uprava bodovania'));
    }

    public function authorizedEdit() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'edit', $this->getSelectedContest()));
    }

    public function titleSubmits() {
        $this->setTitle(_('Submity'));
    }

    public function authorizedSubmits() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'submits', $this->getSelectedContest()));
    }

    public function titleClose() {
        $this->setTitle(_('Uzavierka bodovania'));
    }

    public function authorizedClose() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'close', $this->getSelectedContest()));
    }

    public function titleDefault() {
        $this->setTitle(_('Fykosí Fyzikláni'));
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'default', $this->getSelectedContest()));
    }

    public function titleResults() {
        $this->setTitle(_('Výsledky Fykosího Fyzikláni'));
    }

    public function authorizedResults() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('fyziklani', 'results', $this->getSelectedContest()));
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

    public function entryFormSucceeded(Form $form) {
        Debugger::timer();
        $values = $form->getValues();
        if ($this->checkTaskCode($values->taskCode, $msg)) {
            $teamID = $this->taskCodePreprocessor->extractTeamID($values->taskCode);
            $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($values->taskCode);
            $taskID = $this->taskLabelToTaskID($taskLabel);
            $r = $this->database->query('INSERT INTO ' . \DbNames::TAB_FYZIKLANI_SUBMIT, ['points' => $values->points, 'fyziklani_task_id' => $taskID, 'e_fyziklani_team_id' => $teamID]);
            $t = Debugger::timer();
            if ($r) {
                $this->flashMessage(_('Body boli uložené. (' . $values->points . ' bodů, tým ID ' . $teamID . ', ' . $t . 's)'), 'success');
                $this->redirect(':Org:Fyziklani:entry');
            } else {
                $this->flashMessage(_('Vyskytla sa chyba'), 'danger');
            }
        } else {
            $this->flashMessage($msg, 'danger');
        }
    }

    /*
        public function entryFormByTaksCodeSucceeded(Form $form) {
            foreach ($form->getComponents() as $control) {
                if ($control instanceof \Nette\Forms\Controls\SubmitButton) {
                    if ($control->isSubmittedBy()) {
                        $points = substr($control->getName(), 6);
                    }
                }
            }
        }*/

    public function editFormSucceeded(Form $form) {
        $values = $form->getValues();

        $teamID = $this->submitToTeam($values->submit_id);
        if (!$teamID) {
            $this->flashMessage(_('Submit neexistuje'), 'danger');
            $this->redirect(':Org:Fyziklani:submits');
        }

        /* Uzatvorené bodovanie nejde editovať; */
        if (!$this->isOpenSubmit($teamID)) {
            $this->flashMessage(_('Bodovanie tohoto týmu je uzavreté'), 'danger');
            $this->redirect(':Org:Fyziklani:submits');
        }
        try {
            $this->database->query('UPDATE ' . \DbNames::TAB_FYZIKLANI_SUBMIT . ' SET ? where fyziklani_submit_id=?', ['points' => $values->points], $values->submit_id);
            $this->flashMessage(_('Body boli zmenené'), 'success');
            $this->redirect('this');
        } catch (\Exception $e) {
            $this->flashMessage('ops', 'danger');
            Debugger::log($e);
        }
    }

    public function submitExist($taskID, $teamID) {
        return (bool)$this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('fyziklani_task_id=?', $taskID)->where('e_fyziklani_team_id=?', $teamID)->count();
    }

    public function teamExist($teamID) {
        return $this->database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->get($teamID)->event_id == $this->eventID;
    }

    /** Vrati true ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getCurrentEvent() ? true : false;
    }


    public function taskLabelToTaskID($taskLabel) {
        $row = $this->database->table(\DbNames::TAB_FYZIKLANI_TASK)->where('label = ?', $taskLabel)->where('event_id = ?', $this->eventID)->fetch();
        if ($row) {
            return $row->fyziklani_task_id;
        }
        return false;
    }

    public function getCurrentEventID() {
        return $this->getCurrentEvent()->event_id;
    }

    /** vráti paramtre daného eventu */
    public function getCurrentEvent() {
        return $this->database->table(\DbNames::TAB_EVENT)->where('year', $this->year)->where('event_type_id', $this->container->parameters['fyziklani']['eventTypeID'])->fetch();
    }

    private function getSubmit($submitID) {
        return $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('fyziklani_submit_id', $submitID)->fetch();
    }

    public function submitToTeam($submitID) {
        $r = $this->getSubmit($submitID);
        return $r ? $r->e_fyziklani_team_id : $r;
    }

    public function isOpenSubmit($teamID) {
        $points = $this->database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->where('e_fyziklani_team_id', $teamID)->fetch()->points;
        return !is_numeric($points);
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

    public function checkTaskCode($taskCode, &$msg) {
        /** skontroluje pratnosť kontrolu */
        if (!$this->taskCodePreprocessor->checkControlNumber($taskCode)) {
            $msg = _('Chybne zadaný kód úlohy.');
            return false;
        }
        /* Existenica týmu */
        $teamID = $this->taskCodePreprocessor->extractTeamID($taskCode);

        if (!$this->teamExist($teamID)) {
            $msg = _('Team ' . $teamID . ' nexistuje');
            return false;
        }
        /* otvorenie submitu */
        if (!$this->isOpenSubmit($teamID)) {

            $msg = _('Bodovanie tohoto týmu je uzavreté');
            return false;
        }
        /* správny label */
        $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($taskCode);
        $taskID = $this->taskLabelToTaskID($taskLabel);
        if (!$taskID) {
            $msg = 'Úloha  ' . $taskLabel . ' nexistuje';
            return false;
        }
        /* Nezadal sa duplicitne toto nieje editácia */
        if ($this->submitExist($taskID, $teamID)) {
            $msg = 'Úloha ' . $taskLabel . ' už bola zadaná';
            return false;
        }
        return true;
    }
}
