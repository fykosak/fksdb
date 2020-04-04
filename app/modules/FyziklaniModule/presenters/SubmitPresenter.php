<?php

namespace FyziklaniModule;

use EventModule\EventEntityTrait;
use FKSDB\Components\Controls\Entity\Fyziklani\Submit\EditControl;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\AllSubmitsGrid;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\PointsMismatchException;
use FKSDB\NotImplementedException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;

/**
 * Class SubmitPresenter
 * @package FyziklaniModule
 * @method ModelFyziklaniSubmit getEntity()
 */
class SubmitPresenter extends BasePresenter {
    use EventEntityTrait;

    /* ***** Title methods *****/
    public function titleEntry() {
        $this->setTitle(_('Zadávání bodů'), 'fa fa-pencil-square-o');
    }

    public function titleList() {
        $this->setTitle(_('Submits'), 'fa fa-table');
    }

    public function titleEdit() {
        $this->setTitle(_('Úprava bodování'), 'fa fa-pencil');
    }

    public function titleDetail() {
        $this->setTitle(sprintf(_('Detail of the submit #%d'), $this->getEntity()->fyziklani_submit_id), 'fa fa-pencil');
    }

    /* ***** Authorized methods *****/
    public function authorizedEntry() {
        $this->authorizedCreate();
    }

    /**
     * @inheritDoc
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isEventOrContestOrgAuthorized($resource, $privilege);
    }

    /* ******** ACTION METHODS ********/

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function actionEdit(int $id) {
        $this->traitActionEdit($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderDetail(int $id) {
        $this->template->model = $this->loadEntity($id);
    }

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
     * @return SubmitsGrid
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentGrid(): SubmitsGrid {
        return new AllSubmitsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
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
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     * @throws AbortException
     */
    public function createComponentEditForm(): Control {
        return new EditControl($this->getContext(), $this->getEvent());
    }
}
