<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Components\Controls\Entity\FyziklaniSubmitEditComponent;
use FKSDB\Components\Controls\Fyziklani\Submit\PointsEntryComponent;
use FKSDB\Components\Grids\Fyziklani\Submits\AllSubmitsGrid;
use FKSDB\Model\Entity\ModelNotFoundException;
use FKSDB\Model\Events\Exceptions\EventNotFoundException;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Model\Fyziklani\Submit\HandlerFactory;
use FKSDB\Model\Logging\FlashMessageDump;
use FKSDB\Model\Logging\MemoryLogger;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\Model\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\Model\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class SubmitPresenter
 * *
 * @method ModelFyziklaniSubmit getEntity()
 */
class SubmitPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    protected HandlerFactory $handlerFactory;

    final public function injectHandlerFactory(HandlerFactory $handlerFactory): void {
        $this->handlerFactory = $handlerFactory;
    }

    /* ***** Title methods *****/
    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('Scoring'), 'fa fa-pencil-square-o'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Submits'), 'fa fa-table'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(_('Change of scoring'), 'fa fa-pencil'));
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Detail of the submit #%d'), $this->getEntity()->fyziklani_submit_id), 'fa fa-pencil'));
    }

    /* ***** Authorized methods *****/

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isEventOrContestOrgAuthorized($resource, $privilege);
    }

    /* ******** ACTION METHODS ********/

    /**
     * @return void
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function renderEdit(): void {
        $this->template->model = $this->getEntity();
    }

    /* ****** COMPONENTS **********/
    /**
     * @return AllSubmitsGrid
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): AllSubmitsGrid {
        return new AllSubmitsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return PointsEntryComponent
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): PointsEntryComponent {
        return new PointsEntryComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @return FyziklaniSubmitEditComponent
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): FyziklaniSubmitEditComponent {
        return new FyziklaniSubmitEditComponent($this->getContext(), $this->getEvent(), $this->getEntity());
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadTypeException
     * @throws ClosedSubmittingException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function handleCheck(): void {
        $logger = new MemoryLogger();
        $handler = $this->handlerFactory->create($this->getEvent());
        $handler->checkSubmit($logger, $this->getEntity(), $this->getEntity()->points);
        FlashMessageDump::dump($logger, $this);
        $this->redirect('this');
    }

    protected function getORMService(): ServiceFyziklaniSubmit {
        return $this->serviceFyziklaniSubmit;
    }
}
