<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\ScheduleGroupFormComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\AllPersonsGrid;
use FKSDB\Components\Grids\Schedule\GroupListComponent;
use FKSDB\Components\Grids\Schedule\GroupsGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method ScheduleGroupModel getEntity()
 */
class ScheduleGroupPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private ScheduleGroupService $scheduleGroupService;

    final public function injectServiceScheduleGroup(ScheduleGroupService $scheduleGroupService): void
    {
        $this->scheduleGroupService = $scheduleGroupService;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Schedule'), 'fas fa-list');
    }

    public function titlePersons(): PageTitle
    {
        return new PageTitle(null, _('Whole program'), 'fas fa-list');
    }

    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, _('Schedule items'), 'fas fa-clipboard-list');
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

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): BaseGrid
    {
        return new GroupsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentList(): GroupListComponent
    {
        return new GroupListComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentAllPersonsGrid(): AllPersonsGrid
    {
        return new AllPersonsGrid($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentItemsGrid(): ItemsGrid
    {
        return new ItemsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ScheduleGroupService
    {
        return $this->scheduleGroupService;
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}
