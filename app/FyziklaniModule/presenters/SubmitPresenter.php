<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\FyziklaniSubmitsGrid;
use FKSDB\model\Fyziklani\TaskCodeException;
use FKSDB\model\Fyziklani\TaskCodeHandler;
use FKSDB\model\Fyziklani\TaskCodeHandlerFactory;
use ModelFyziklaniSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\Button;
use ORM\Models\Events\ModelFyziklaniTeam;

class SubmitPresenter extends BasePresenter {

    /**
     *
     * @var ModelFyziklaniSubmit
     */
    private $editSubmit;
    /**
     * @var TaskCodeHandlerFactory
     */
    private $taskCodeHandlerFactory;

    public function injectTaskCodeHandlerFactory(TaskCodeHandlerFactory $taskCodeHandlerFactory) {
        $this->taskCodeHandlerFactory = $taskCodeHandlerFactory;
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
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
        $handler = $this->getTaskCodeHandler();
        try {
            $handler->checkTaskCode($code);
        } catch (TaskCodeException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
            return;
        }
        /**
         * @var $form Form
         */
        $form = $this['entryQRForm']->getForm();
        $form->setDefaults(['taskCode' => $code]);
        foreach ($this->getGameSetup()->getAvailablePoints() as $points) {
            /**
             * @var $button Button
             */
            $button = $form['points' . $points];
            $button->setDisabled(false);
        }
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
        $team = ModelFyziklaniTeam::createFromTableRow($this->serviceFyziklaniTeam->findByPrimary($teamId));
        if (!$team->hasOpenSubmit()) {
            $this->flashMessage(_('Bodování tohoto týmu je uzavřené.'), 'danger');
            $this->backlinkRedirect();
            $this->redirect('table'); // if there's no backlink
        }
        $submit = $this->editSubmit;
        $this->template->fyziklani_submit_id = $submit ? true : false;
        /**
         * @var $control FormControl
         */
        $control = $this['submitEditForm'];
        $control->getForm()->setDefaults([
            'team_id' => $submit->e_fyziklani_team_id,
            'task' => $submit->getTask()->label,
            'points' => $submit->points,
            'team' => $submit->getTeam()->name,
        ]);
    }

    public function titleEntry() {
        $this->setTitle(_('Zadávání bodů'));
        $this->setIcon('fa fa-pencil-square-o');
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
        $this->setIcon('fa fa-pencil');
    }

    public function authorizedEdit() {
        $this->authorizedEntry();
    }

    public function titleTable() {
        $this->setTitle(_('Submits'));
        $this->setIcon('fa fa-table');
    }

    public function authorizedTable() {
        $this->authorizedEntry();
    }

    private function getTaskCodeHandler(): TaskCodeHandler {
        return $this->taskCodeHandlerFactory->createHandler($this->getEvent());
    }

    public function createComponentEntryForm() {
        return $this->fyziklaniFactory->createEntryForm($this->container, $this->getEvent());
    }

    public function createComponentEntryQRForm() {
        $form = $this->fyziklaniFactory->createEntryQRForm($this->getGameSetup());

        $form->getForm()->onSuccess[] = function (Form $form) {
            $this->entryFormSucceeded($form);
        };
        return $form;
    }

    /**
     * @return FyziklaniSubmitsGrid
     * @throws BadRequestException
     */
    public function createComponentSubmitsGrid() {
        return new FyziklaniSubmitsGrid($this->getEvent(), $this->serviceFyziklaniSubmit);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentSubmitEditForm() {
        $control = $this->fyziklaniFactory->createEditForm($this->getGameSetup());
        $control->getForm()->onSuccess[] = function (Form $form) {
            $this->editFormSucceeded($form);
        };
        return $control;
    }


    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    private function entryFormSucceeded(Form $form) {
        $handler = $this->getTaskCodeHandler();
        $values = $form->getValues();
        $httpData = $form->getHttpData();

        $points = 0;
        foreach ($httpData as $key => $value) {
            if (preg_match('/points([0-9])/', $key, $match)) {
                $points = +$match[1];
            }
        }
        try {
            $log = $handler->preProcess($values->taskCode, $points);
            $this->flashMessage($log, 'success');
            $this->redirect('table');
        } catch (TaskCodeException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    private function editFormSucceeded(Form $form) {
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
}
