<?php

namespace FyziklaniModule;

use EventModule\EventEntityTrait;
use FKSDB\Components\Controls\Entity\Fyziklani\Submit\EditControl;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\AllSubmitsGrid;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use FKSDB\Fyziklani\ClosedSubmittingException;
use FKSDB\Fyziklani\PointsMismatchException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;

/**
 * Class SubmitPresenter
 * *
 * @method ModelFyziklaniSubmit getEntity()
 */
class SubmitPresenter extends BasePresenter {
    use EventEntityTrait;

    /* ***** Title methods *****/
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCreate() {
        $this->setTitle(_('Zadávání bodů'), 'fa fa-pencil-square-o');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setTitle(_('Submits'), 'fa fa-table');
    }

    /**
     * @throws BadRequestException
     */
    public function titleEdit() {
        $this->setTitle(_('Úprava bodování'), 'fa fa-pencil');
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail() {
        $this->setTitle(sprintf(_('Detail of the submit #%d'), $this->getEntity()->fyziklani_submit_id), 'fa fa-pencil');
    }

    /* ***** Authorized methods *****/

    /**
     * @inheritDoc
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isEventOrContestOrgAuthorized($resource, $privilege);
    }

    /* ******** ACTION METHODS ********/

    /**
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
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
     * @return SubmitsGrid
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentGrid(): SubmitsGrid {
        return new AllSubmitsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return Control
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentCreateForm(): Control {
        return new TaskCodeInput($this->getContext(), $this->getEvent());
    }

    /**
     * @inheritDoc
     * @throws AbortException
     */
    public function createComponentEditForm(): Control {
        return new EditControl($this->getContext(), $this->getEvent());
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
}
