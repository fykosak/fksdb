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
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;

/**
 * Class SubmitPresenter
 * *
 * @method ModelFyziklaniSubmit getEntity()
 * @method ModelFyziklaniSubmit loadEntity(int $id)
 */
class SubmitPresenter extends BasePresenter {
    use EventEntityTrait;

    /* ***** Title methods *****/
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCreate(): void {
        $this->setTitle(_('Zadávání bodů'), 'fa fa-pencil-square-o');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList(): void {
        $this->setTitle(_('Submits'), 'fa fa-table');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleEdit(): void {
        $this->setTitle(_('Úprava bodování'), 'fa fa-pencil');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail(int $id): void {
        $this->setTitle(sprintf(_('Detail of the submit #%d'), $this->loadEntity($id)->fyziklani_submit_id), 'fa fa-pencil');
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
     * @param int $id
     * @throws BadRequestException
     */
    public function actionEdit(int $id): void {
        $this->traitActionEdit($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderDetail(int $id): void {
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderEdit(int $id): void {
        $this->template->model = $this->loadEntity($id);
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
     * @return Control
     * @throws AbortException
     * @throws BadRequestException
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
    public function handleCheck(): void {
        $log = $this->getServiceFyziklaniSubmit()->checkSubmit($this->getEntity(), $this->getEntity()->points, $this->getUser());
        $this->flashMessage($log->getMessage(), $log->getLevel());
        $this->redirect('this');
    }

    protected function getORMService(): ServiceFyziklaniSubmit {
        return $this->getServiceFyziklaniSubmit();
    }
}
