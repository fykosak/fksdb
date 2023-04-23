<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\EntityForms\FyziklaniSubmitFormComponent;
use FKSDB\Components\Game\Submits\AllSubmitsGrid;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\Form\CtyrbojPointsEntryComponent;
use FKSDB\Components\Game\Submits\Form\FOFPointsEntryComponent;
use FKSDB\Components\Game\Submits\Form\PointsEntryComponent;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method SubmitModel getEntity()
 */
class SubmitPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    /* ***** Title methods *****/
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Scoring'), 'fas fa-pen');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Submits'), 'fa fa-table');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Change of scoring'), 'fas fa-pen');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Detail of the submit #%d'), $this->getEntity()->fyziklani_submit_id),
            'fas fa-search'
        );
    }

    /* ***** Authorized methods *****/

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    /* ******** ACTION METHODS ********/

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws ClosedSubmittingException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function handleCheck(): void
    {
        $handler = $this->getEvent()->createGameHandler($this->getContext());
        $handler->check($this->getEntity(), $this->getEntity()->points);
        FlashMessageDump::dump($handler->logger, $this);
        $this->redirect('this');
    }

    /* ****** COMPONENTS **********/

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): AllSubmitsGrid
    {
        return new AllSubmitsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): PointsEntryComponent
    {
        switch ($this->getEvent()->event_type_id) {
            case 1:
                return new FOFPointsEntryComponent($this->getContext(), $this->getEvent());
            case 17:
                return new CtyrbojPointsEntryComponent($this->getContext(), $this->getEvent());
        }
        throw new NotImplementedException();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): FyziklaniSubmitFormComponent
    {
        return new FyziklaniSubmitFormComponent($this->getContext(), $this->getEntity());
    }


    protected function getORMService(): SubmitService
    {
        return $this->submitService;
    }
}
