<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Schedule\Forms\ScheduleGroupForm;
use FKSDB\Components\Schedule\ItemGrid;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class GroupPresenter extends BasePresenter
{
    /** @phpstan-use EventEntityPresenterTrait<ScheduleGroupModel> */
    use EventEntityPresenterTrait;

    private ScheduleGroupService $service;

    final public function injectService(ScheduleGroupService $service): void
    {
        $this->service = $service;
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     * @throws ForbiddenRequestException
     * @throws EventNotFoundException
     */
    public function authorizedDetail(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getEntity(), 'detail', $this->getEvent());
    }
    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws \ReflectionException
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
     * @throws \ReflectionException
     * @throws ForbiddenRequestException
     * @throws EventNotFoundException
     */
    public function authorizedEdit(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getEntity(), 'edit', $this->getEvent());
    }
    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
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
        return $this->eventAuthorizator->isAllowed(
            new PseudoEventResource(ScheduleGroupModel::RESOURCE_ID, $this->getEvent()),
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

    protected function getORMService(): ScheduleGroupService
    {
        return $this->service;
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
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): ScheduleGroupForm
    {
        return new ScheduleGroupForm($this->getEvent(), $this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentItemsGrid(): ItemGrid
    {
        return new ItemGrid($this->getContext(), $this->getEntity());
    }
}
