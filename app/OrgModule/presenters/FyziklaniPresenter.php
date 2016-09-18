<?php

namespace OrgModule;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use \Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

class FyziklaniPresenter extends \OrgModule\BasePresenter {

    const TABLE_FYZIKLANI_TEAM = 'e_fyziklani_team';
    const TABLE_FYZIKLANI_TASK = 'fyziklani_task';
    const TABLE_FYZIKLANI_SUBMIT = 'fyziklani_submit';

    private $submit;
    private $closeSubmitStragegy;

    /**
     *
     * @var Nette\Database\Context 
     */
    private $database;

    public function __construct(\Nette\Database\Connection $database/* ,\Fyziklani\CloseSubmitStragegy $closeSubmitStragegy */) {
        // $this->closeSubmitStragegy = $closeSubmitStragegy;
        $this->database = $database;


        parent::__construct();
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

            $this->template->submits = $this->database->table(self::TABLE_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id',$id);
        }
    }

    public function createComponentSubmitsGrid() {
        $grid = new \FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid($this->database);
        return $grid;
    }

    public function createComponentCloseGrid() {
        $grid = new \FKSDB\Components\Grids\Fyziklani\FyziklaniTeamsGrid($this->database);
        // $grid->setSearchable(true);

        return $grid;
    }

    public function createComponentFyziklaniCloseForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('e_fyziklani_team_id',0);
        $form->addText('next_task',_('Úloha u vydávačov'))->setDisabled();
        $form->addCheckbox('next_task_correct',_('Úloha u vydávačov sa zhaduje'))
                ->setRequired(_('Skontrolujte prosím zhodnosť úlohy u vydávačov'));
        $form->addCheckbox('submit_task_correct',_('Úlohy a počty bodov sú správne'))
                ->setRequired(_('Skontrolujte prosím správnosť zadania bodov!'));

        $form->addSubmit('send','Potvrdiť spravnosť');
        $form->onSuccess[] = [$this,'closeFormSucceeded'];
        return $form;
    }

    public function closeFormSucceeded(Form $form) {
        $values = $form->getValues();
        $submits = $this->database->table(self::TABLE_FYZIKLANI_SUBMIT)
                ->select('*')
                ->where('e_fyziklani_team_id',$values->e_fyziklani_team_id);
        \Nette\Diagnostics\Debugger::barDump($submits);
        $sum = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
        }
        if($this->database->query('UPDATE '.self::TABLE_FYZIKLANI_TEAM.' SET ? WHERE e_fyziklani_team_id=? ',['points' => $sum],$values->e_fyziklani_team_id)){
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

        $this->submit = $this->database->table('fyziklani_submit')
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

    public function titleDafault() {
        $this->setTitle(_('Pultik Fyzikláni'));
    }
    
     public function titleClose() {
        $this->setTitle(_('Uzavierka bodovania'));
    }

    public function entryFormSucceeded(\Nette\Application\UI\Form $form) {
        $values = $form->getValues();
        $year = 2016;

        $numLabel = $this->getNumLabel($values->taskCode);

        if($numLabel % 9){
            $this->flashMessage('Chybne zadaný kód úlohy.','danger');
            return;
        }
        $teamID = $this->extractTeamID($values->taskCode);
        if(!$this->teamExist($teamID,$year)){
            $this->flashMessage('Team '.$teamID.' nexistuje','danger');
            return;
        }
        $taskLabel = $this->extractTaksLabel($values->taskCode);
        $taksID = $this->taskLabetToTaskID($taskLabel,$year);
        if(!$taksID){
            $this->flashMessage('Úloha  '.$taskLabel.' nexistuje','danger');
            return;
        }
        if($this->submitExist($taksID,$teamID,$year)){
            $this->flashMessage('Úloha '.$taskLabel.' už bola zadaná','warning');
            return;
        }
        if($this->database->query('INSERT INTO '.self::TABLE_FYZIKLANI_SUBMIT,[
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

    public function editFormSucceeded(\Nette\Application\UI\Form $form) {
        $values = $form->getValues();
        if($this->database->query('UPDATE '.self::TABLE_FYZIKLANI_SUBMIT.' SET ? where fyziklani_submit_id=?',[
                    'points' => $values->points
                        ],$values->submit_id)){
            $this->flashMessage('Body boli zmenene','success');
        }else{
            $this->flashMessage('ops','danger');
        }

        $this->redirect('this');
    }

    public function submitExist($taksID,$teamID) {
        return (bool) $this->database->table(self::TABLE_FYZIKLANI_SUBMIT)
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

    public function taskLabetToTaskID($taskLabel,$year) {
        $row = $this->database->table(self::TABLE_FYZIKLANI_TASK)
                ->where('label = ?',$taskLabel)
                ->where('event_id = ?',$this->getCurrentEventID($year))
                ->fetch();
        if($row){
            return $row->fyziklani_task_id;
        }

        return false;
    }

    public function teamExist($teamID,$year) {
        return $this->database->table(self::TABLE_FYZIKLANI_TEAM)
                        ->get($teamID)->event_id == $this->getCurrentEventID($year);
    }

    public function getCurrentEventID() {
        \Nette\Diagnostics\Debugger::barDump($this);
        $event = $this->getActualEvent();
        if($event){
            return $event->event_id;
        }
        return false;
    }

    public function getActualEvent() {
        return $this->database->table('event')->where('year=?',$this->year)->where('event_type_id=?',1)->fetch();
    }

    public function eventExist() {
        return $this->getActualEvent() ? true : false;
    }

}
