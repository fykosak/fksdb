<?php

namespace OrgModule;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use \ServiceFyziklaniSubmit;

class FyziklaniPresenter extends \OrgModule\BasePresenter {

    const TABLE_FYZIKLANI_TEAM = 'e_fyziklani_team';
    const TABLE_FYZIKLANI_TASK = 'fyziklani_task';
    const TABLE_FYZIKLANI_SUBMIT = 'fyziklani_submit';

    private $ServiceFyziklaniSubmit;
    private $submit_id;

    /**
     *
     * @var Nette\Database\Context 
     */
    private $database;

    public function __construct(\Nette\Database\Connection $database) {

        $this->database = $database;


        parent::__construct();
    }

    public function renderEntry() {
        
    }

    public function renderDefault() {
        
    }

    public function renderEdit($id) {
        $this->submit_id = $id;
    }

    public function createComponentSubmitsGrid() {
        $grid = new \FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid($this->database);


        return $grid;
    }

    public function createComponentFyziklaniEditForm() {
        $submit = $this->database->table('fyziklani_submit')
                        ->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name')
                        ->where('fyziklani_submit_id = ?',$this->submit_id)->fetch();
        \Nette\Diagnostics\Debugger::barDump($submit);
        if(!$submit){
            $this->flashMessage('Submit neexistuje');

            return;
        }

        $form = new \Nette\Application\UI\Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('submit_id',$this->submit_id);

        $form->addText('team',_('Tým'))
                ->setValue($submit->name)
                ->setDisabled(true);
        $form->addText('team_id',_('Tým ID'))
                ->setValue($submit->e_fyziklani_team_id)
                ->setDisabled(true);
        $form->addText('task',_('Úloha'))
                ->setValue($submit->label)
                ->setDisabled(true);
        $form->addRadioList('points',_('Počet bodů'),array(5 => 5,3 => 3,2 => 2,1 => 1))->setDefaultValue($submit->points);
        $form->addSubmit('send','Uložit');
        // $form->onSuccess[] = [$this,'entryFormSucceeded'];
        return $form;
    }

    public function createComponentFyziklaniEntryForm($id) {
        \Nette\Diagnostics\Debugger::barDump($id);

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
        $this->database->query('INSERT INTO '.self::TABLE_FYZIKLANI_SUBMIT,[
            'points' => $values->points,
            'fyziklani_task_id' => $taksID,
            'e_fyziklani_team_id' => $teamID
        ]);
        $this->redirect('this');
    }

    public function submitExist($taksID,$teamID,$year) {
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
        return $this->database->table(self::TABLE_FYZIKLANI_TASK)
                        ->where('label = ?',$taskLabel)
                        ->where('event_id = ?',$this->getCurrentEventID($year))
                        ->fetch()
                ->fyziklani_task_id;
    }

    public function teamExist($teamID,$year) {
        return $this->database->table(self::TABLE_FYZIKLANI_TEAM)
                        ->get($teamID)->event_id == $this->getCurrentEventID($year);
    }

    public function getCurrentEventID($year) {
        return 95;
    }

//put your code here
}
