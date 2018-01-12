<?php

namespace FyziklaniModule;

use FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid;
use FKSDB\model\Fyziklani\TaskCodePreprocessor;
use ModelFyziklaniSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use ORM\Models\Events\ModelFyziklaniTeam;

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
                /**
                 * @var $form Form
                 */
                $form = $this['entryForm'];
                $form->setDefaults(['taskCode' => $id]);
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
        $teams = [];
        foreach ($this->serviceFyziklaniTeam->findParticipating($this->getEventId()) as $team) {
            /**
             * @var $team ModelFyziklaniTeam
             */
            $teams[] = [
                'team_id' => $team->e_fyziklani_team_id,
                'name' => $team->name,
            ];
        };
        $tasks = [];
        foreach ($this->serviceFyziklaniTask->findAll($this->getEventId()) as $task) {
            /**
             * @var $task \ModelFyziklaniTask
             */
            $tasks[] = [
                'task_id' => $task->fyziklani_task_id,
                'label' => $task->label
            ];
        };
        $form = $this->fyziklaniFactory->createEntryForm($this->getEvent(), $teams, $tasks);
        $form->onSuccess[] = [$this, 'entryFormSucceeded'];
        return $form;
    }

    public function entryFormSucceeded(Form $form) {
        $values = $form->getValues();
        if ($this->checkTaskCode($values->taskCode, $msg)) {
            $points = 0;
            foreach ($form->getComponents() as $control) {
                if ($control instanceof SubmitButton) {
                    if ($control->isSubmittedBy()) {
                        $points = substr($control->getName(), 6);
                    }
                }
            }
            $teamID = $this->taskCodePreprocessor->extractTeamID($values->taskCode);
            $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($values->taskCode);
            $taskID = $this->serviceFyziklaniTask->taskLabelToTaskID($taskLabel, $this->getEventId());

            if (is_null($submit = $this->serviceFyziklaniSubmit->findByTaskAndTeam($taskID, $teamID))) {
                $submit = $this->serviceFyziklaniSubmit->createNew([
                    'points' => $points,
                    'fyziklani_task_id' => $taskID,
                    'e_fyziklani_team_id' => $teamID,
                    /* ugly, force current timestamp in database
                     * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                     */
                    'created' => null
                ]);
            } else {
                // $submit = $this->serviceFyziklaniSubmit->findByTaskAndTeam($teamID,$taskID);
                $this->serviceFyziklaniSubmit->updateModel($submit, [
                    'points' => $points,
                    /* ugly, exclude previous value of `modified` from query
                     * so that `modified` is set automatically by DB
                     * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                     */
                    'modified' => null
                ]);
                $this->serviceFyziklaniSubmit->save($submit);
            }
            /**
             * @var $team ModelFyziklaniTeam
             */
            $team = $this->serviceFyziklaniTeam->findByPrimary($teamID);
            $teamName = $team->name;

            /**
             * @var $task \ModelFyziklaniTask
             */
            $task = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->getEventId());
            $taskName = $task->name;

            try {
                $this->serviceFyziklaniSubmit->save($submit);
                $this->flashMessage(sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'), $points, $teamName, $teamID, $taskLabel, $taskName), 'success');
                $this->redirect('this');
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


        if (!$this->serviceFyziklaniTeam->teamExist($teamID, $this->getEventId())) {
            $msg = sprintf(_('Tým %s neexistuje.'), $teamID);
            return false;
        }
        /**
         * @var $team ModelFyziklaniTeam
         */
        $team = $this->serviceFyziklaniTeam->findByPrimary($teamID);
        /* otvorenie submitu */
        if (!$team->hasOpenSubmit()) {
            $msg = _('Bodování tohoto týmu je uzavřené.');
            return false;
        }
        /* správny label */
        $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($taskCode);
        $taskID = $this->serviceFyziklaniTask->taskLabelToTaskID($taskLabel, $this->getEventId());
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
        $form = $this->fyziklaniFactory->createEditForm($this->getEvent());
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
        /**
         * @var $team ModelFyziklaniTeam
         */
        $team = $this->serviceFyziklaniTeam->findByPrimary($teamID);
        if (!$team->hasOpenSubmit()) {
            $this->flashMessage(_('Bodování tohoto týmu je uzavřené.'), 'danger');
            $this->backlinkRedirect();
            $this->redirect('table'); // if there's no backlink
        }
        $submit = $this->editSubmit;
        $this->template->fyziklani_submit_id = $submit ? true : false;
        /**
         * @var $form Form
         */
        $form = $this['fyziklaniEditForm'];
        $form->setDefaults([
            'team_id' => $submit->e_fyziklani_team_id,
            'task' => $submit->getTask()->label,
            'points' => $submit->points,
            'team' => $submit->getTeam()->name,
        ]);
    }

    public function editFormSucceeded(Form $form) {
        $values = $form->getValues();

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
        $this->backlinkRedirect();
        $this->redirect('table'); // if there's no backlink
    }

    public function createComponentSubmitsGrid() {
        return new FyziklaniSubmitsGrid($this->getEventId(), $this->serviceFyziklaniSubmit, $this->serviceFyziklaniTeam);
    }

}
