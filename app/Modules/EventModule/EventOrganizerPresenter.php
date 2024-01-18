<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\EventOrganizerFormComponent;
use FKSDB\Components\Grids\EventOrganizer\EventOrganizersGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Services\EventOrganizerService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

final class EventOrganizerPresenter extends BasePresenter
{
    /** @use EventEntityPresenterTrait<EventOrganizerModel> */
    use EventEntityPresenterTrait;

    private EventOrganizerService $service;

    final public function injectServiceEventOrganizer(EventOrganizerService $service): void
    {
        $this->service = $service;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Organizers of event'), 'fas fa-user-tie');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create organizer of event'), 'fas fa-user-plus');
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
            'fas fa-user-edit'
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
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $this->getEvent());
    }

    protected function getORMService(): EventOrganizerService
    {
        return $this->service;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): EventOrganizersGrid
    {
        return new EventOrganizersGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): EventOrganizerFormComponent
    {
        return new EventOrganizerFormComponent($this->getContext(), $this->getEvent(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): EventOrganizerFormComponent
    {
        return new EventOrganizerFormComponent($this->getContext(), $this->getEvent(), $this->getEntity());
    }
}
