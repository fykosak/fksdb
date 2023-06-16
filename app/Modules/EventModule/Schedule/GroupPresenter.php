<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\EntityForms\ScheduleGroupFormComponent;
use FKSDB\Components\Schedule\Attendance\GroupAttendanceFormComponent;
use FKSDB\Components\Schedule\GroupListComponent;
use FKSDB\Components\Schedule\ItemsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Modules\EventModule\BasePresenter;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\Utils\UI\Title;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method ScheduleGroupModel getEntity()
 */
class GroupPresenter extends BasePresenter
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

    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, _('Schedule items'), 'fas fa-clipboard-list');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit schedule group "%s"'), $this->getEntity()->getName()[$this->getLang()]),
            'fas fa-pen'
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function titleAttendance(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Attendance for group "%s"'), $this->getEntity()->getName()[$this->getLang()]),
            'fas fa-user-check'
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
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    public function renderList(): void
    {
        $this->template->items = [
            new NavItem(new Title(null, _('Create group'), 'fa fa-plus'), 'create'),
            new NavItem(new Title(null, _('All persons'), 'fa fa-users'), ':Schedule:PersonSchedule:list'),
        ];
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
    protected function createComponentGrid(): GroupListComponent
    {
        return new GroupListComponent($this->getContext(), $this->getEvent());
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

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentAttendance(): GroupAttendanceFormComponent
    {
        return new GroupAttendanceFormComponent($this->getContext(), $this->getEntity());
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
