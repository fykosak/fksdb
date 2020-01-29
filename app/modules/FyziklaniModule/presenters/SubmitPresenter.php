<?php

namespace FyziklaniModule;

use EventModule\EventEntityTrait;
use FKSDB\Components\Controls\Fyziklani\EditControl;
use FKSDB\Components\Controls\Fyziklani\Submit\QREntryControl;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\AllSubmitsGrid;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\PointsMismatchException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Class SubmitPresenter
 * @package FyziklaniModule
 * @method ModelFyziklaniSubmit getEntity()
 */
class SubmitPresenter extends BasePresenter {
    use EventEntityTrait;

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

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail() {
        $this->setTitle(sprintf(_('Detail of the submit #%d'), $this->getEntity()->fyziklani_submit_id));
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
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionEdit(int $id) {
        $team = $this->loadEntity($id);
        $control = $this->getComponent('editControl');
        if (!$control instanceof EditControl) {
            throw new BadRequestException();
        }
        $control->setSubmit($team);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail(int $id) {
        $this->loadEntity($id);
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderEdit() {
        $this->template->model = $this->getEntity();
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
        return new AllSubmitsGrid(
            $this->getEvent(),
            $this->getServiceFyziklaniTask(),
            $this->getServiceFyziklaniSubmit(),
            $this->getServiceFyziklaniTeam(),
            $this->getTableReflectionFactory()
        );
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
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     */
    public function handleCheck() {
        $log = $this->getServiceFyziklaniSubmit()->checkSubmit($this->getEntity(), $this->getEntity()->points, $this->getUser());
        $this->flashMessage($log->getMessage(), $log->getLevel());
        $this->redirect('this');
    }

    /**
     * @inheritDoc
     */
    protected function getORMService() {
        return $this->getServiceFyziklaniSubmit();
    }

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return 'fyziklani.submit';
    }
}
