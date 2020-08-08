<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Fyziklani\Submit\HandlerFactory;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Components\Controls\Entity\Fyziklani\Submit\EditComponent;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\AllSubmitsGrid;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\UI\PageTitle;
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

    /** @var HandlerFactory */
    protected $handlerFactory;

    /**
     * @param HandlerFactory $handlerFactory
     * @return void
     */
    public function injectHandlerFactory(HandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    /* ***** Title methods *****/
    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     */
    public function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('Zadávání bodů'), 'fa fa-pencil-square-o'));
    }

    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     */
    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Submits'), 'fa fa-table'));
    }

    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(_('Úprava bodování'), 'fa fa-pencil'));
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
     * @param string $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isEventOrContestOrgAuthorized($resource, $privilege);
    }

    /* ******** ACTION METHODS ********/

    /**
     * @return void
     * @throws BadTypeException
     * @throws ModelNotFoundException
     */
    public function actionEdit(): void {
        $this->traitActionEdit();
    }

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
     * @return SubmitsGrid
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): SubmitsGrid {
        return new AllSubmitsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return TaskCodeInput
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): TaskCodeInput {
        return new TaskCodeInput($this->getContext(), $this->getEvent());
    }

    /**
     * @return EditComponent
     * @throws EventNotFoundException
     */
    protected function createComponentEditForm(): EditComponent {
        return new EditComponent($this->getContext(), $this->getEvent());
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
        return $this->getServiceFyziklaniSubmit();
    }
}
