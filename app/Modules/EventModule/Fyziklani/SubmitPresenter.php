<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Controls\Fyziklani\Submit\PointsEntryComponent;
use FKSDB\Components\EntityForms\FyziklaniSubmitFormComponent;
use FKSDB\Components\Grids\Fyziklani\Submits\AllSubmitsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Models\Fyziklani\Submit\Handler;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
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
     */
    public function handleCheck(): void
    {
        $logger = new MemoryLogger();
        $handler = new Handler($this->getEvent(), $this->getContext());
        $handler->checkSubmit($logger, $this->getEntity(), $this->getEntity()->points);
        FlashMessageDump::dump($logger, $this);
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
     */
    protected function createComponentCreateForm(): PointsEntryComponent
    {
        return new PointsEntryComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
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
