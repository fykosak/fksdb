<?php

namespace FyziklaniModule;

use FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid;
use FKSDB\model\Fyziklani\TaskCodePreprocessor;
use ModelFyziklaniSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
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

    private function savePoints($fullCode, $points) {
        $teamID = $this->taskCodePreprocessor->extractTeamID($fullCode);
        $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($fullCode);
        $taskID = $this->serviceFyziklaniTask->taskLabelToTaskID($taskLabel, $this->eventID);

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
        $teamName = $this->serviceFyziklaniTeam->findByPrimary($teamID)->name;
        $taskName = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->eventID)->name;

        try {
            $this->serviceFyziklaniSubmit->save($submit);
            return [sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'), $points, $teamName, $teamID, $taskLabel, $taskName), 'success'];
            // $this->redirect('this');
        } catch (Exception $e) {
            Debugger::log($e);
            return [_('Vyskytla se chyba'), 'danger'];

        }
    }

    public function renderEntry() {
        if ($this->isAjax()) {

            $fullCode = $this->getHttpRequest()->getQuery('fullCode');
            $points = $this->getHttpRequest()->getQuery('points');
            if ($this->checkTaskCode($fullCode, $msg)) {
                $msg = $this->savePoints($fullCode, $points);
            } else {
                $msg = [$msg, 'danger'];
            }
            $this->sendResponse(new JsonResponse($msg));
        }
    }

    public function createComponentEntryForm() {
        $teams = [];
        foreach ($this->serviceFyziklaniTeam->findParticipating($this->eventID) as $team) {
            $teams[] = [
                'team_id' => $team->e_fyziklani_team_id,
                'name' => $team->name,
            ];
        };
        $tasks = [];
        foreach ($this->serviceFyziklaniTask->findAll($this->eventID) as $task) {
            $tasks[] = [
                'task_id' => $task->fyziklani_task_id,
                'label' => $task->label,
                'name' => $task->name,
            ];
        };
        $form = $this->fyziklaniFactory->createEntryForm($teams, $tasks);
      //  $form->onSuccess[] = [$this, 'entryFormSucceeded'];
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
            $taskID = $this->serviceFyziklaniTask->taskLabelToTaskID($taskLabel, $this->eventID);

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
            $teamName = $this->serviceFyziklaniTeam->findByPrimary($teamID)->name;
            $taskName = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->eventID)->name;

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
        $form = $this->fyziklaniFactory->createEditForm($this->getCurrentEvent());
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
            $this->backlinkRedirect();
            $this->redirect('table'); // if there's no backlink
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
        return new FyziklaniSubmitsGrid($this->eventID, $this->serviceFyziklaniSubmit, $this->serviceFyziklaniTeam);
    }

}
