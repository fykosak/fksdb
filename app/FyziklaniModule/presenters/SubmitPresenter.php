<?php

namespace FyziklaniModule;

use FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid;
use FKSDB\model\Fyziklani\TaskCodePreprocessor;
use ModelFyziklaniSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\Button;
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


    public function actionQrEntry($id) {
        if (!$id) {
            $this->flashMessage('Code is required', 'danger');
            return;
        }
        $l = strlen($id);
        if ($l > 9) {
            $this->flashMessage('Code is too long', 'danger');
            return;
        }
        $code = str_repeat('0', 9 - $l) . strtoupper($id);
        if ($this->checkTaskCode($code, $msg)) {
            /**
             * @var $form Form
             */
            $form = $this['entryQRForm'];
            $form->setDefaults(['taskCode' => $code]);
            foreach ($this->getEvent()->getParameter('availablePoints') as $points) {
                /**
                 * @var $button Button
                 */
                $button = $form['points' . $points];
                $button->setDisabled(false);
            }
        } else {
            $this->flashMessage($msg, 'danger');
        }
    }


    public function titleEntry() {
        $this->setTitle(_('Zadávání bodů'));
    }

    public function titleQrEntry() {
        $this->titleEntry();
    }

    public function titleAutoClose() {
        $this->titleEntry();
    }

    public function authorizedEntry() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'submit')));
    }

    public function authorizedQrEntry() {
        $this->authorizedEntry();
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
        $teamId = $this->taskCodePreprocessor->extractTeamId($fullCode);
        $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($fullCode);
        $taskId = $this->serviceFyziklaniTask->taskLabelToTaskId($taskLabel, $this->getEventId());

        if (is_null($submit = $this->serviceFyziklaniSubmit->findByTaskAndTeam($taskId, $teamId))) {
            $submit = $this->serviceFyziklaniSubmit->createNew([
                'points' => $points,
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                /* ugly, force current timestamp in database
                 * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                 */
                'created' => null
            ]);
        } else {
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
        $team = $this->serviceFyziklaniTeam->findByPrimary($teamId);

        $taskName = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->getEventId())->name;

        try {
            $this->serviceFyziklaniSubmit->save($submit);
            return [sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'), $points, $team->name, $teamId, $taskLabel, $taskName), 'success'];
        } catch (\Exception $e) {
            Debugger::log($e);
            return [_('Vyskytla se chyba'), 'danger'];

        }
    }

    /**
     * @throws \Nette\Application\AbortException
     */
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
        $teams = $this->serviceFyziklaniTeam->getTeams($this->getEventId());
        $tasks = $this->serviceFyziklaniTask->getTasks($this->getEventId());

        $form = $this->fyziklaniFactory->createEntryForm($teams, $tasks);
        return $form;
    }

    public function createComponentEntryQRForm() {
        $form = $this->fyziklaniFactory->createEntryQRForm($this->getEvent());

        $form->onSuccess[] = [$this, 'entryFormSucceeded'];
        return $form;
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    public function entryFormSucceeded(Form $form) {
        $values = $form->getValues();
        $httpData = $form->getHttpData();

        if ($this->checkTaskCode($values->taskCode, $msg)) {
            $points = 0;
            foreach ($httpData as $key => $value) {
                if (preg_match('/points([0-9])/', $key, $match)) {
                    $points = +$match[1];
                }
            }
            $log = $this->savePoints($values->taskCode, $points);
            $this->flashMessage($log[0], $log[1]);
            $this->redirect('autoClose');
        } else {
            $this->flashMessage($msg, 'danger');
            // $this->redirect('this');
        }
    }

    public function checkTaskCode($taskCode, &$msg) {
        /** skontroluje pratnosť kontrolu */
        if (!$this->taskCodePreprocessor->checkControlNumber($taskCode)) {
            $msg = _('Chybně zadaný kód úlohy.');
            return false;
        }
        /* Existenica týmu */
        $teamId = $this->taskCodePreprocessor->extractTeamId($taskCode);


        if (!$this->serviceFyziklaniTeam->teamExist($teamId, $this->getEventId())) {
            $msg = sprintf(_('Tým %s neexistuje.'), $teamId);
            return false;
        }
        /**
         * @var $team ModelFyziklaniTeam
         */
        $team = $this->serviceFyziklaniTeam->findByPrimary($teamId);
        /* otvorenie submitu */
        if (!$team->hasOpenSubmit()) {
            $msg = _('Bodování tohoto týmu je uzavřené.');
            return false;
        }
        /* správny label */
        $taskLabel = $this->taskCodePreprocessor->extractTaskLabel($taskCode);
        $taskId = $this->serviceFyziklaniTask->taskLabelToTaskId($taskLabel, $this->getEventId());
        if (!$taskId) {
            $msg = sprintf(_('Úloha %s neexistuje.'), $taskLabel);
            return false;
        }
        /* Nezadal sa duplicitne toto nieje editácia */
        if ($this->serviceFyziklaniSubmit->submitExist($taskId, $teamId)) {
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

    /**
     * @param $id
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit($id) {
        $this->editSubmit = $this->serviceFyziklaniSubmit->findByPrimary($id);

        if (!$this->editSubmit) {
            throw new BadRequestException(_('Neexistující submit.'), 404);
        }

        $teamId = $this->editSubmit->e_fyziklani_team_id;

        /* Uzatvorené bodovanie nejde editovať; */
        /**
         * @var $team ModelFyziklaniTeam
         */
        $team = $this->serviceFyziklaniTeam->findByPrimary($teamId);
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

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
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
