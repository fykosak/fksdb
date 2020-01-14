<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\EditControl;
use FKSDB\Components\Controls\Fyziklani\Submit\DetailControl;
use FKSDB\Components\Controls\Fyziklani\Submit\QREntryControl;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class SubmitPresenter
 * @package FyziklaniModule
 */
class SubmitPresenter extends BasePresenter {
    /**
     * @var ModelFyziklaniSubmit
     */
    private $submit;

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

    public function titleDetail() {
        $this->setTitle(_('Submit detail'));
        $this->setIcon('fa fa-pencil');
    }

    /* ***** Authorized methods *****/
    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedEntry() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.submit', 'default'));
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedDetail() {
        $this->authorizedEntry();
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedQrEntry() {
        $this->authorizedEntry();
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedEdit() {
        $this->authorizedEntry();
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedList() {
        $this->authorizedEntry();
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedAutoClose() {
        $this->authorizedEntry();
    }
    /* ******** ACTION METHODS ********/
    /**
     * @param $id
     * @throws BadRequestException
     */
    public function actionQrEntry($id) {
        if (!$id) {
            $this->flashMessage('Code is required', \BasePresenter::FLASH_ERROR);
            return;
        }
        $control = $this->getComponent('entryQRControl');
        if (!$control instanceof QREntryControl) {
            throw new BadRequestException();
        }
        $control->setCode($id);
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws AbortException
     * @throws \ReflectionException
     */
    public function actionEdit($id) {
        $control = $this->getComponent('editControl');
        if (!$control instanceof EditControl) {
            throw new BadRequestException();
        }
        $submit = $this->loadModel($id);
        $control->setSubmit($submit);
    }

    /**
     * @param $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws \ReflectionException
     */
    public function actionDetail($id) {
        $control = $this->getComponent('detailControl');
        if (!$control instanceof DetailControl) {
            throw new BadRequestException();
        }
        $submit = $this->loadModel($id);
        $control->setSubmit($submit);
    }

    /**
     * @param int $id
     * @return ModelFyziklaniSubmit
     * @throws AbortException
     * @throws \ReflectionException
     */
    private function loadModel(int $id): ModelFyziklaniSubmit {
        if ($this->submit) {
            return $this->submit;
        }
        $row = $this->getServiceFyziklaniSubmit()->findByPrimary($id);
        if (!$row) {
            $this->flashMessage(_('Submit neexistuje'), \BasePresenter::FLASH_ERROR);
            $this->backLinkRedirect();
            $this->redirect('list');
        }
        $this->submit = ModelFyziklaniSubmit::createFromActiveRow($row);
        return $this->submit;
    }



    /* ****** COMPONENTS **********/
    /**
     * @return TaskCodeInput
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentEntryControl(): TaskCodeInput {
        return $this->fyziklaniComponentsFactory->createTaskCodeInput($this->getEvent());
    }

    /**
     * @return QREntryControl
     * @throws BadRequestException
     * @throws AbortException
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
     * @throws AbortException
     */
    public function createComponentGrid(): SubmitsGrid {
        return $this->fyziklaniComponentsFactory->createSubmitsGrid($this->getEvent());
    }

    /**
     * @return EditControl
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentEditControl(): EditControl {
        $control = $this->fyziklaniComponentsFactory->createEditSubmitControl($this->getEvent());
        $control->getForm()->onSuccess[] = function () {
            $this->redirect('list');
        };
        return $control;
    }

    /**
     * @return DetailControl
     */
    public function createComponentDetailControl(): DetailControl {
        return $this->fyziklaniComponentsFactory->createSubmitDetailControl();
    }
}
