<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\EditSubmitControl;
use FKSDB\Components\Controls\Fyziklani\QREntryControl;
use FKSDB\Components\Controls\Fyziklani\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use Nette\Application\BadRequestException;

class SubmitPresenter extends BasePresenter {

    /* ***** Title methods *****/
    public function titleEntry() {
        $this->setTitle(_('Zadávání bodů'));
        $this->setIcon('fa fa-pencil-square-o');
    }

    public function titleQrEntry() {
        $this->titleEntry();
    }

    public function titleAutoClose() {
        $this->setTitle(_('You can close this page'));
        $this->setIcon('fa fa-pencil-square-o');
    }

    public function titleList() {
        $this->setTitle(_('Submits'));
        $this->setIcon('fa fa-table');
    }

    public function titleEdit() {
        $this->setTitle(_('Úprava bodování'));
        $this->setIcon('fa fa-pencil');
    }

    /* ***** Authorized methods *****/
    public function authorizedEntry() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.submit', 'default'));
    }

    public function authorizedQrEntry() {
        $this->authorizedEntry();
    }

    public function authorizedEdit() {
        $this->authorizedEntry();
    }

    public function authorizedList() {
        $this->authorizedEntry();
    }

    public function authorizedAutoClose() {
        $this->authorizedEntry();
    }
    /* ******** ACTION METHODS ********/
    /**
     * @param $id
     */
    public function actionQrEntry($id) {
        if (!$id) {
            $this->flashMessage('Code is required', \BasePresenter::FLASH_ERROR);
            return;
        }
        /**
         * @var $control QREntryControl
         */
        $control = $this->getComponent('entryQRControl');
        $control->setCode($id);
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit($id) {
        /**
         * @var $control EditSubmitControl
         */
        $control = $this->getComponent('editControl');
        try {
            $control->setSubmit(+$id);
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('list');
        }
    }

    /* ****** COMPONENTS **********/
    /**
     * @return TaskCodeInput
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentEntryControl(): TaskCodeInput {
        return $this->fyziklaniComponentsFactory->createTaskCodeInput($this->getEvent());
    }

    /**
     * @return QREntryControl
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentEntryQRControl(): QREntryControl {
        $control = $this->fyziklaniComponentsFactory->createQREntryControl($this->getEvent());
        $control->getForm()->onSuccess[] = function () {
            $this->redirect('autoClose');
        };
        return $control;
    }

    /**
     * @return SubmitsGrid
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentGrid(): SubmitsGrid {
        return $this->fyziklaniComponentsFactory->createSubmitsGrid($this->getEvent());
    }

    /**
     * @return EditSubmitControl
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentEditControl(): EditSubmitControl {
        $control = $this->fyziklaniComponentsFactory->createEditSubmitControl($this->getEvent());
        $control->getForm()->onSuccess[] = function () {
            $this->redirect('list');
        };
        return $control;
    }
}
