<?php

namespace FyziklaniModule;

use FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid;
use FKSDB\model\Fyziklani\TaskCodePreprocessor;
use ModelFyziklaniSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;

class SubmitPresenter extends BasePresenter {

    /**
     * @var TaskCodePreprocessor
     */
    private $taskCodePreprocessor;

    /**
     *
     * @var ModelFyziklaniSubmit
     */
    private $editSubmit;

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
                'e_fyziklani_team_id' => $teamID,
                /* ugly, force current timestamp in database
                 * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                 */
                'created' => null
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
        $this->editSubmit = $this->serviceFyziklaniSubmit->findByPrimary($id);

        if (!$this->editSubmit) {
            throw new BadRequestException(_('Neexistující submit.'), 404);
        }

        $teamID = $this->editSubmit->e_fyziklani_team_id;

        /* Uzatvorené bodovanie nejde editovať; */
        if (!$this->serviceFyziklaniTeam->isOpenSubmit($teamID)) {
            $this->flashMessage(_('Bodování tohoto týmu je uzavřené.'), 'danger');
            $this->redirect(':Fyziklani:Submit:table');
        }
        $submit = $this->editSubmit;
        $this->template->fyziklani_submit_id = $submit ? true : false;
        $this['fyziklaniEditForm']->setDefaults([
            'team_id' => $submit->e_fyziklani_team_id,
            'task' => $submit->getTask()->label,
            'points' => $submit->points,
            'team' => $submit->getTeam()->name,
        ]);
    }

    public function editFormSucceeded(Form $form) {
        $values = $form->getValues();

        $teamID = $this->editSubmit->e_fyziklani_team_id;

        /* Uzatvorené bodovanie nejde editovať; */
        if (!$this->serviceFyziklaniTeam->isOpenSubmit($teamID)) {
            $this->flashMessage(_('Bodování tohoto týmu je uzavřené.'), 'danger');
            $this->redirect(':Fyziklani:Submit:table');
        }
        $submit = $this->editSubmit;
        $this->serviceFyziklaniSubmit->updateModel($submit, [
            'points' => $values->points,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null
        ]);
        $this->serviceFyziklaniSubmit->save($submit);
        $this->flashMessage(_('Body byly změněny.'), 'success');
        $this->redirect(':Fyziklani:Submit:table');
    }

    public function createComponentSubmitsGrid() {
        return new FyziklaniSubmitsGrid($this->eventID, $this->serviceFyziklaniSubmit, $this->serviceFyziklaniTeam);
    }

}
