<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\EventOrganizerFormComponent;
use FKSDB\Components\Grids\EventOrganizer\EventOrganizersGrid;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Services\EventOrganizerService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;

final class EventOrganizerPresenter extends BasePresenter
{
    /** @use EntityPresenterTrait<EventOrganizerModel> */
    use EntityPresenterTrait;

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(EventOrganizerModel::ResourceId, $this->getEvent()),
            'list',
            $this->getEvent()
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Organizers of event'), 'fas fa-user-tie');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(EventOrganizerModel::ResourceId, $this->getEvent()),
            'create',
            $this->getEvent()
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create organizer of event'), 'fas fa-user-plus');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws EventNotFoundException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEntity()),
            'edit',
            $this->getEvent()
        );
    }
    /**
     * @throws EventNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws NotFoundException
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
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NotFoundException
     */
    public function authorizedDelete(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEntity()),
            'delete',
            $this->getEvent()
        );
    }

    public function titleDelete(): PageTitle
    {
        return new PageTitle(null, _('Remove event organizer'), 'fas fa-user-edit');
    }

    public function actionDelete(): void
    {
        try {
            $this->getORMService()->disposeModel($this->getEntity());
            $this->flashMessage(_('Entity has been deleted'), Message::LVL_WARNING);
            $this->redirect('list');
        } catch (BadRequestException $exception) {
            $this->flashMessage(_('Error during deleting'), Message::LVL_ERROR);
            $this->redirect('list');
        }
    }

    /**
     * @throws GoneException
     */
    protected function getORMService(): EventOrganizerService
    {
        throw new GoneException();
    }

    protected function loadModel(): EventOrganizerModel
    {
        /** @var EventOrganizerModel|null $candidate */
        $candidate = $this->getEvent()->getEventOrganizers()->where('e_org_id', $this->id)->fetch();
        if ($candidate) {
            return $candidate;
        } else {
            throw new NotFoundException(_('Model does not exist.'));
        }
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
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): EventOrganizerFormComponent
    {
        return new EventOrganizerFormComponent($this->getContext(), $this->getEvent(), $this->getEntity());
    }
}
