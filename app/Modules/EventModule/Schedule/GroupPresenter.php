<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Schedule\Forms\ScheduleGroupForm;
use FKSDB\Components\Schedule\ItemGrid;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;

final class GroupPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<ScheduleGroupModel> */
    use EntityPresenterTrait;

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws EventNotFoundException
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEntity()),
            'detail',
            $this->getEvent()
        );
    }
    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Group: %s'), $this->getEntity()->name->getText($this->translator->lang)),
            $this->getEntity()->schedule_group_type->getIconName()
        );
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
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
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit group: %s'), $this->getEntity()->name->getText($this->translator->lang)),
            'fas fa-pen'
        );
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(ScheduleGroupModel::RESOURCE_ID, $this->getEvent()),
            'create',
            $this->getEvent()
        );
    }

    /**
     * @throws CannotAccessModelException
     */
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create group'), 'fas fa-pen');
    }

    protected function loadModel(): ScheduleGroupModel
    {
        /** @var ScheduleGroupModel|null $candidate */
        $candidate = $this->getEvent()->getScheduleGroups()->where('schedule_group_id', $this->id)->fetch();
        if ($candidate) {
            return $candidate;
        } else {
            throw new NotFoundException(_('Model does not exist.'));
        }
    }

    /**
     * @throws GoneException
     */
    protected function getORMService(): ScheduleGroupService
    {
        throw new GoneException();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): ScheduleGroupForm
    {
        return new ScheduleGroupForm($this->getEvent(), $this->getContext(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    protected function createComponentEditForm(): ScheduleGroupForm
    {
        return new ScheduleGroupForm($this->getEvent(), $this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    protected function createComponentItemsGrid(): ItemGrid
    {
        return new ItemGrid($this->getContext(), $this->getEntity());
    }
}
