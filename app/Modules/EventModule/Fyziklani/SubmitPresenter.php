<?php

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Controls\Entity\FyziklaniSubmitEditComponent;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInputComponent;
use FKSDB\Components\Grids\Fyziklani\Submits\AllSubmitsGrid;
use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Models\Fyziklani\Submit\HandlerFactory;
use FKSDB\Models\Logging\FlashMessageDump;
use FKSDB\Models\Logging\MemoryLogger;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class SubmitPresenter
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
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
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
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    /**
     * @return void
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
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
     * @return TaskCodeInputComponent
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): TaskCodeInputComponent {
        return new TaskCodeInputComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @return FyziklaniSubmitEditComponent
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentEditForm(): FyziklaniSubmitEditComponent {
        return new FyziklaniSubmitEditComponent($this->getContext(), $this->getEvent(), $this->getEntity());
    }

    /**
     * @return void
     * @throws ClosedSubmittingException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
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
