<?php

namespace FyziklaniModule;

use Nette\Application\BadRequestException;
use \Nette\Application\UI\Form;
use \Nette\Diagnostics\Debugger;
use \FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid;
use FKSDB\model\Fyziklani\TaskCodePreprocessor;

class SubmitPresenter extends BasePresenter {
    /**
     * @var TaskCodePreprocessor
     */
    private $taskCodePreprocessor;

    public function __construct(TaskCodePreprocessor $taskCodePreprocessor) {
        parent::__construct();
        $this->taskCodePreprocessor = $taskCodePreprocessor;
    }

    public function actionEntry($id) {
        if ($id) {
            if ($this->checkTaskCode($id, $msg)) {
                $this['entryForm']->setDefaults(['taskCode' => $id]);
            } else {
                $this->flashMessage($msg, 'danger');
                $this->redirect(':Fyziklani:Submit:entry');
            }
        }
    }

    public function titleEntry() {
        $this->setTitle(_('Zadávaní bodů'));
    }

    public function authorizedEntry() {
        $this->setAuthorized($this->getEventAuthorizator()->isAllowed('fyziklani', 'submit', $this->getCurrentEvent()));
    }

    public function titleEdit() {
        $this->setTitle(_('Uprava bodovania'));
    }

    public function authorizedEdit() {
        $this->authorizedEntry();
    }

    public function titleTable() {
        $this->setTitle(_('Submits'));
    }

    public function authorizedTable() {
        $this->authorizedEntry();
    }

    public function createComponentEntryForm() {
        $form = $this->fyziklaniFactory->createEntryForm();
        $form->onSuccess[] = [$this, 'entryFormSucceeded'];
        return $form;
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


    public function entryFormSucceeded(Form $form) {
        Debugger::timer();
        $values = $form->getValues();
        if ($this->checkTaskCode($values->taskCode, $msg)) {
            $teamID = $this->taskCodePreprocessor->extractTeamID($values->taskCode);
            $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($values->taskCode);
            $taskID = $this->taskLabelToTaskID($taskLabel);
            $submit = $this->serviceFyziklaniSubmit->createNew([
                'points' => $values->points, 
                'fyziklani_task_id' => $taskID, 
                'e_fyziklani_team_id' => $teamID
            ]);
            try{
                $this->serviceFyziklaniSubmit->save($submit);
                $t = Debugger::timer();
                $this->flashMessage(_('Body boli uložené. (' . $values->points . ' bodů, tým ID ' . $teamID . ', ' . $t . 's)'), 'success');
                $this->redirect(':Fyziklani:submit:entry');
            }
            catch(Exception $e) {
                $this->flashMessage(_('Vyskytla sa chyba'), 'danger');
                Debugger::log($e);
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

    public function createComponentFyziklaniEditForm() {
        $form = $this->fyziklaniFactory->createEditForm();
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
            $this->redirect(':Fyziklani:submit:table');
        }
        /* Uzatvorené bodovanie nejde editovať; */
        if (!$this->isOpenSubmit($teamID)) {
            $this->flashMessage(_('Bodovaní tohto týmu je uzvřené'), 'danger');
            $this->redirect(':Fyziklani:Submit:table');
        }
        $submit = $this->serviceFyziklaniSubmit->findByPrimary($id);
        $this->template->fyziklani_submit_id = $submit ? true : false;
        $this['fyziklaniEditForm']->setDefaults([
            'team_id' => $submit->e_fyziklani_team_id, 
            'task' => $submit->getTask()->label, 
            'points' => $submit->points, 
            'team' => $submit->getTeam()->name,
            'submit_id' => $submit->fyziklani_submit_id
        ]);
    }

    public function editFormSucceeded(Form $form) {
        $values = $form->getValues();

        $teamID = $this->submitToTeam($values->submit_id);
        if (!$teamID) {
            $this->flashMessage(_('Submit neexistuje'), 'danger');
            $this->redirect(':Fyziklani:Submit:table');
        }

        /* Uzatvorené bodovanie nejde editovať; */
        if (!$this->isOpenSubmit($teamID)) {
            $this->flashMessage(_('Bodovanie tohoto týmu je uzavreté'), 'danger');
            $this->redirect(':Fyziklani:Submit:table');
        }
        $submit = $this->serviceFyziklaniSubmit->findByPrimary($values->submit_id);
        $this->serviceFyziklaniSubmit->updateModel($submit, ['points' => $values->points]);
        $this->serviceFyziklaniSubmit->save($submit);
        $this->flashMessage(_('Body boli zmenené'), 'success');
        $this->redirect(':Fyziklani:Submit:table');

    }

    public function createComponentSubmitsGrid() {
        return new FyziklaniSubmitsGrid($this->eventID, $this, $this->serviceFyziklaniSubmit);
    }
}
