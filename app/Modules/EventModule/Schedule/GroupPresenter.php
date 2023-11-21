<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\EntityForms\ScheduleGroupFormComponent;
use FKSDB\Components\Schedule\ItemGrid;
use FKSDB\Components\Schedule\ScheduleList;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\Utils\UI\Title;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

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

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Group: %s'), $this->getEntity()->name->getText($this->translator->lang)),
            'fas fa-clipboard-list'
        );
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
            \sprintf(_('Edit group: %s'), $this->getEntity()->name->getText($this->translator->lang)),
            'fas fa-pen'
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Schedule'), 'fas fa-list');
    }

    public function renderList(): void
    {
        $this->template->items = [
            new NavItem(new Title(null, _('Create group'), 'fas fa-plus'), 'create'),
            new NavItem(new Title(null, _('All persons'), 'fas fa-users'), ':Schedule:Person:list'),
        ];
    }

    protected function getORMService(): ScheduleGroupService
    {
        return $this->service;
    }

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
    protected function createComponentCreateForm(): ScheduleGroupFormComponent
    {
        return new ScheduleGroupFormComponent($this->getEvent(), $this->getContext(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): ScheduleGroupFormComponent
    {
        return new ScheduleGroupFormComponent($this->getEvent(), $this->getContext(), $this->getEntity());
    }

    protected function createComponentGrid(): ScheduleList
    {
        throw new GoneException();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentItemsGrid(): ItemGrid
    {
        return new ItemGrid($this->getContext(), $this->getEntity());
    }
}
