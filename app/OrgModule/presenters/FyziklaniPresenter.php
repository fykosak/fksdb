<?php

namespace OrgModule;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use \Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

class FyziklaniPresenter extends \OrgModule\BasePresenter {

    private $submit;

    const EVENT_TYPE_ID = 1;

    /**
     *
     * @var Nette\Database\Context 
     */
    public $database;
    public $event;
    public $eventID;

    public function __construct(\Nette\Database\Connection $database) {
        parent::__construct();

        $this->database = $database;
    }

    public function startup() {
        if(!$this->eventExist()){
            throw new \Nette\Application\BadRequestException('Pre tento ročník nebolo najduté Fyzikláni',404);
        }
        $this->event = $this->getActualEvent();
        $this->eventID = $this->getCurrentEventID();
        parent::startup();
    }

    public function renderEntry() {
        
    }

    public function renderDefault() {
        
    }

    public function renderEdit() {
        
    }

    public function renderClose($id) {
        $this->template->id = $id;
        if($id){
            $this->template->submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id',$id);
        }
    }

    public function createComponentSubmitsGrid() {
        $grid = new \FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid($this);
        return $grid;
    }

    public function createComponentCloseGrid() {
        $grid = new \FKSDB\Components\Grids\Fyziklani\FyziklaniTeamsGrid($this->database);
        return $grid;
    }

    public function createComponentFyziklaniCloseForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('e_fyziklani_team_id',0);
        $form->addCheckbox('submit_task_correct',_('Úlohy a počty bodov sú správne'))
                ->setRequired(_('Skontrolujte prosím správnosť zadania bodov!'));
        $form->addText('next_task',_('Úloha u vydávačov'))->setDisabled();
        $form->addCheckbox('next_task_correct',_('Úloha u vydávačov sa zhaduje'))
                ->setRequired(_('Skontrolujte prosím zhodnosť úlohy u vydávačov'));


        $form->addSubmit('send','Potvrdiť spravnosť');
        $form->onSuccess[] = [$this,'closeFormSucceeded'];
        return $form;
    }

    public function closeFormSucceeded(Form $form) {
        $values = $form->getValues();
        $submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)
                ->select('*')
                ->where('e_fyziklani_team_id',$values->e_fyziklani_team_id);
        \Nette\Diagnostics\Debugger::barDump($submits);
        $sum = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
        }
        if($this->database->query('UPDATE '.\DbNames::TAB_E_FYZIKLANI_TEAM.' SET ? WHERE e_fyziklani_team_id=? ',['points' => $sum],$values->e_fyziklani_team_id)){
            $this->redirect(':Org:Fyziklani:close');
        };
    }

    public function actionClose($id) {
        if($id){
            $this['fyziklaniCloseForm']->setDefaults(['e_fyziklani_team_id' => $id,'next_task' => 'AB']);
        }
    }

    public function createComponentFyziklaniEditForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('submit_id',0);
        $form->addText('team',_('Tým'))
                ->setDisabled(true);
        $form->addText('team_id',_('Tým ID'))
                ->setDisabled(true);
        $form->addText('task',_('Úloha'))
                ->setDisabled(true);
        $form->addRadioList('points',_('Počet bodů'),array(5 => 5,3 => 3,2 => 2,1 => 1));

        $form->addSubmit('send','Uložit');
        $form->onSuccess[] = [$this,'editFormSucceeded'];
        return $form;
    }

    public function actionEdit($id) {
        if(!$id){
            throw new \Nette\Application\BadRequestException('ID je povinné',400);
        }
        /* Uzatvorené bodovanie nejde editovať; */
        $teamID = $this->submitToTeam($id);
        if(!$this->isOpenSubmit($teamID)){
            $this->flashMessage('Bodovanie tohoto týmu je uzavreté','danger');
            $this->redirect(':Org:Fyziklani:submits');
        }


        $this->submit = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)
                        ->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name')
                        ->where('fyziklani_submit_id = ?',$id)->fetch();

        $this->template->fyziklani_submit_id = $this->submit ? true : false;
        $this['fyziklaniEditForm']->setDefaults([
            'team_id' => $this->submit->e_fyziklani_team_id,
            'task' => $this->submit->label,
            'points' => $this->submit->points,
            'team' => $this->submit->name,
            'submit_id' => $this->submit->fyziklani_submit_id
        ]);
    }

    public function createComponentFyziklaniEntryForm($id) {
        $form = new \Nette\Application\UI\Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addText('taskCode',_('Kód úlohy'))
                ->setRequired()
                ->addRule(\Nette\Forms\Form::PATTERN,'Nesprávyn tvar','[0-9]{5}[A-Z]{2}[0-9]');
        $form->addRadioList('points',_('Počet bodů'),array(5 => 5,3 => 3,2 => 2,1 => 1));
        $form->addSubmit('send','Uložit');
        $form->onSuccess[] = [$this,'entryFormSucceeded'];
        return $form;
    }

    public function titleEntry() {
        $this->setTitle(_('Zadávaní bodů'));
    }

    public function titleEdit() {
        $this->setTitle(_('Uprava bodovania'));
    }

    public function titleSubmits() {
        $this->setTitle(_('Submity'));
    }

    public function titleClose() {
        $this->setTitle(_('Uzavierka bodovania'));
    }

    public function titleDefault() {
        $this->setTitle(_('Fykosí Fyzikláni'));
    }

    public function entryFormSucceeded(Form $form) {
        $values = $form->getValues();
        $numLabel = $this->getNumLabel($values->taskCode);
        /** @TODO */
        if($this->checkContolNumber($numLabel)){
            $this->flashMessage('Chybne zadaný kód úlohy.','danger');
            $this->redirect('this');
            return;
        }
        /* Existenica týmu */
        $teamID = $this->extractTeamID($values->taskCode);
        if(!$this->teamExist($teamID)){
            $this->flashMessage('Team '.$teamID.' nexistuje','danger');
            $this->redirect('this');
            return;
        }
        /* otvorenie submitu */
        if(!$this->isOpenSubmit($teamID)){
            $this->flashMessage('Bodovanie tohoto týmu je uzavreté','danger');
            $this->redirect('this');
            return;
        }
        /* správny label */
        $taskLabel = $this->extractTaksLabel($values->taskCode);
        $taksID = $this->taskLabelToTaskID($taskLabel);
        if(!$taksID){
            $this->flashMessage('Úloha  '.$taskLabel.' nexistuje','danger');
            $this->redirect('this');
            return;
        }
        /* Nezadal sa duplicitne toto nieje editácia */
        if($this->submitExist($taksID,$teamID)){
            $this->flashMessage('Úloha '.$taskLabel.' už bola zadaná','warning');
            $this->redirect('this');
            return;
        }
        if($this->database->query('INSERT INTO '.\DbNames::TAB_FYZIKLANI_SUBMIT,[
                    'points' => $values->points,
                    'fyziklani_task_id' => $taksID,
                    'e_fyziklani_team_id' => $teamID
                ])){
            $this->flashMessage(_('body boli uložené'),'success');
            $this->redirect('this');
        }else{
            $this->flashMessage('Vyskytla sa chyba','danger');
        }
    }

    public function editFormSucceeded(Form $form) {
        $values = $form->getValues();
        /* Uzatvorené bodovanie nejde editovať; */
        $teamID = $this->submitToTeam($values->submit_id);
        if(!$this->isOpenSubmit($teamID)){
            $this->flashMessage('Bodovanie tohoto týmu je uzavreté','danger');
            $this->redirect(':Org:Fyziklani:submits');
        }
        if($this->database->query('UPDATE '.\DbNames::TAB_FYZIKLANI_SUBMIT.' SET ? where fyziklani_submit_id=?',[
                    'points' => $values->points
                        ],$values->submit_id)){
            $this->flashMessage('Body boli zmenene','success');
        }else{
            $this->flashMessage('ops','danger');
        }

        $this->redirect('this');
    }

    public function submitExist($taksID,$teamID) {
        return (bool) $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)
                        ->where('fyziklani_task_id=?',$taksID)
                        ->where('e_fyziklani_team_id=?',$teamID)
                        ->count();
    }

    public function extractTeamID($numLabel) {
        return (int) substr($numLabel,0,5);
    }

    public function extractTaksLabel($teamTaskLabel) {
        return (string) substr($teamTaskLabel,5,2);
    }

    public function getNumLabel($teamTaskLabel) {
        return str_replace(array('A','B','C','D','E','F','G','H'),array(1,2,3,4,5,6,7,8),$teamTaskLabel);
    }

    public function taskLabelToTaskID($taskLabel,$year) {
        $row = $this->database->table(\DbNames::TAB_FYZIKLANI_TASK)
                ->where('label = ?',$taskLabel)
                ->where('event_id = ?',$this->getCurrentEventID($year))
                ->fetch();
        if($row){
            return $row->fyziklani_task_id;
        }
        return false;
    }

    public function teamExist($teamID) {
        return $this->database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)
                        ->get($teamID)->event_id == $this->eventID;
    }

    public function getCurrentEventID() {
        return $this->getActualEvent()->event_id;
    }

    /** vráti paramtre daného eventu */
    public function getActualEvent() {
        return $this->database->table(\DbNames::TAB_EVENT)
                        ->where('year',$this->year)
                        ->where('event_type_id',self::EVENT_TYPE_ID)
                        ->fetch();
    }

    /** Vrati tru ak pre daný ročník existuje fyzikláni */
    public function eventExist() {
        return $this->getActualEvent() ? true : false;
    }

    private function checkContolNumber($numLabel) {
        return $numLabel % 9;
    }

    public function submitToTeam($submitID) {
        return $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)
                        ->where('fyziklani_submit_id',$submitID)
                        ->fetch()->e_fyziklani_team_id;
    }

    public function isOpenSubmit($teamID) {
        $points = $this->database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)
                        ->where('e_fyziklani_team_id',$teamID)
                        ->fetch()->points;
        Debugger::barDump($points);
        Debugger::barDump(is_numeric($points));
        return !is_numeric($points);
    }

}
