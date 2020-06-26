<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Components\Controls\Entity\Fyziklani\Submit\EditControl;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\AllSubmitsGrid;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use FKSDB\Fyziklani\ClosedSubmittingException;
use FKSDB\Fyziklani\PointsMismatchException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\UI\PageTitle;
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
    use EventEntityPresenterTrait;

    /* ***** Title methods *****/
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCreate() {
        $this->setPageTitle(new PageTitle(_('Zadávání bodů'), 'fa fa-pencil-square-o'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setPageTitle(new PageTitle(_('Submits'), 'fa fa-table'));
    }

    /**
     * @throws BadRequestException
     */
    public function titleEdit() {
        $this->setPageTitle(new PageTitle(_('Úprava bodování'), 'fa fa-pencil'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail() {
        $this->setPageTitle(new PageTitle(sprintf(_('Detail of the submit #%d'), $this->getEntity()->fyziklani_submit_id), 'fa fa-pencil'));
    }

    /* ***** Authorized methods *****/

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
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
    protected function createComponentGrid(): SubmitsGrid {
        return new AllSubmitsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return Control
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentCreateForm(): Control {
        return new TaskCodeInput($this->getContext(), $this->getEvent());
    }

    /**
     * @return Control
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentEditForm(): Control {
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

    protected function getORMService(): ServiceFyziklaniSubmit {
        return $this->getServiceFyziklaniSubmit();
    }
}
