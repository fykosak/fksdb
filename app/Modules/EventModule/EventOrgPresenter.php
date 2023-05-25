<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\EventOrgFormComponent;
use FKSDB\Components\Grids\EventOrg\EventOrgsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\EventOrgModel;
use FKSDB\Models\ORM\Services\EventOrgService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method EventOrgModel getEntity()
 */
class EventOrgPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private EventOrgService $eventOrgService;

    final public function injectServiceEventOrg(EventOrgService $eventOrgService): void
    {
        $this->eventOrgService = $eventOrgService;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Organizers of event'), 'fa fa-user-tie');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create organizer of event'), 'fa fa-user-plus');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit organizer of event "%s"'), $this->getEntity()->person->getFullName()),
            'fa fa-user-edit'
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function actionDelete(): void
    {
        try {
            $this->traitHandleDelete();
            $this->flashMessage(_('Entity has been deleted'), Message::LVL_WARNING);
            $this->redirect('list');
        } catch (BadRequestException $exception) {
            $this->flashMessage(_('Error during deleting'), Message::LVL_ERROR);
            $this->redirect('list');
        }
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    protected function getORMService(): EventOrgService
    {
        return $this->eventOrgService;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): EventOrgsGrid
    {
        return new EventOrgsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): EventOrgFormComponent
    {
        return new EventOrgFormComponent($this->getContext(), $this->getEvent(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): EventOrgFormComponent
    {
        return new EventOrgFormComponent($this->getContext(), $this->getEvent(), $this->getEntity());
    }
}
