<?php

namespace FyziklaniModule;

use Nette\Application\BadRequestException;
use \Nette\Application\UI\Form;
use \Nette\Diagnostics\Debugger;
use \Nette\Forms\Controls\SubmitButton;
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
        $this->setTitle(_('Zadávání bodů'));
    }

    public function authorizedEntry() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'submit')));
    }

    public function titleEdit() {
        $this->setTitle(_('Úprava bodování'));
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
        $form = $this->fyziklaniFactory->createEntryForm($this->eventID);
        $form->onSuccess[] = [$this, 'entryFormSucceeded'];
        return $form;
    }

    public function entryFormSucceeded(Form $form) {
        Debugger::timer();
        $values = $form->getValues();
        if ($this->checkTaskCode($values->taskCode, $msg)) {
            foreach ($form->getComponents() as $control) {
                if ($control instanceof SubmitButton) {
                    if ($control->isSubmittedBy()) {
                        $points = substr($control->getName(), 6);
                    }
                }
            }
            $teamID = $this->taskCodePreprocessor->extractTeamID($values->taskCode);
            $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($values->taskCode);
            $taskID = $this->serviceFyziklaniTask->taskLabelToTaskID($taskLabel, $this->eventID);
            $submit = $this->serviceFyziklaniSubmit->createNew([
                'points' => $points,
                'fyziklani_task_id' => $taskID,
                'e_fyziklani_team_id' => $teamID
            ]);
            try {
                $this->serviceFyziklaniSubmit->save($submit);
                $t = Debugger::timer();
                $this->flashMessage(_('Body byly uloženy. (' . $points . ' bodů, tým ID ' . $teamID . ', ' . $t . 's)'), 'success');
                $this->redirect(':Fyziklani:submit:entry');
            } catch (Exception $e) {
                $this->flashMessage(_('Vyskytla se chyba'), 'danger');
                Debugger::log($e);
            }
        } else {
            $this->flashMessage($msg, 'danger');
        }
    }

    public function checkTaskCode($taskCode, &$msg) {
        /** skontroluje pratnosť kontrolu */
        if (!$this->taskCodePreprocessor->checkControlNumber($taskCode)) {
            $msg = _('Chybně zadaný kód úlohy.');
            return false;
        }
        /* Existenica týmu */
        $teamID = $this->taskCodePreprocessor->extractTeamID($taskCode);

        if (!$this->serviceFyziklaniTeam->teamExist($teamID, $this->eventID)) {
            $msg = sprintf(_('Tým %s neexistuje.'), $teamID);
            return false;
        }
        /* otvorenie submitu */
        if (!$this->serviceFyziklaniTeam->isOpenSubmit($teamID)) {
            $msg = _('Bodování tohoto týmu je uzavřené.');
            return false;
        }
        /* správny label */
        $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($taskCode);
        $taskID = $this->serviceFyziklaniTask->taskLabelToTaskID($taskLabel, $this->eventID);
        if (!$taskID) {
            $msg = sprintf(_('Úloha %s neexistuje.'), $taskLabel);
            return false;
        }
        /* Nezadal sa duplicitne toto nieje editácia */
        if ($this->serviceFyziklaniSubmit->submitExist($taskID, $teamID)) {
            $msg = sprintf(_('Úloha %s už byla zadaná.'), $taskLabel);
            return false;
        }
        return true;
    }

    public function createComponentFyziklaniEditForm() {
        $form = $this->fyziklaniFactory->createEditForm($this->eventID);
        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function actionEdit($id) {
        if (!$id) {
            throw new BadRequestException('ID je povinné.', 400);
        }
        /* Neexitujúci submit nejde editovať */
        $teamID = $this->serviceFyziklaniSubmit->findByPrimary($id)->e_fyziklani_team_id;
        if (!$teamID) {
            $this->flashMessage(_('Submit neexistuje.'), 'danger');
            $this->redirect(':Fyziklani:submit:table');
        }
        /* Uzatvorené bodovanie nejde editovať; */
        if (!$this->serviceFyziklaniTeam->isOpenSubmit($teamID)) {
            $this->flashMessage(_('Bodování tohoto týmu je uzavřené.'), 'danger');
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

        $teamID = $this->serviceFyziklaniSubmit->findByPrimary($values->submit_id)->e_fyziklani_team_id;
        if (!$teamID) {
            $this->flashMessage(_('Submit neexistuje.'), 'danger');
            $this->redirect(':Fyziklani:Submit:table');
        }

        /* Uzatvorené bodovanie nejde editovať; */
        if (!$this->serviceFyziklaniTeam->isOpenSubmit($teamID)) {
            $this->flashMessage(_('Bodování tohoto týmu je uzavřené.'), 'danger');
            $this->redirect(':Fyziklani:Submit:table');
        }
        $submit = $this->serviceFyziklaniSubmit->findByPrimary($values->submit_id);
        $this->serviceFyziklaniSubmit->updateModel($submit, ['points' => $values->points]);
        $this->serviceFyziklaniSubmit->save($submit);
        $this->flashMessage(_('Body byly změněny.'), 'success');
        $this->redirect(':Fyziklani:Submit:table');
    }

    public function createComponentSubmitsGrid() {
        return new FyziklaniSubmitsGrid($this->eventID, $this->serviceFyziklaniSubmit, $this->serviceFyziklaniTeam);
    }

}
