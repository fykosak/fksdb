<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\ScheduleGroupFormComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\AllPersonsGrid;
use FKSDB\Components\Grids\Schedule\GroupsGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\Models\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method ModelScheduleGroup getEntity()
 */
class ScheduleGroupPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private ServiceScheduleGroup $serviceScheduleGroup;

    final public function injectServiceScheduleGroup(ServiceScheduleGroup $serviceScheduleGroup): void
    {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
    }

    /**

     * @throws ForbiddenRequestException
     */
    public function titleList(): void
    {
        $this->setPageTitle(new PageTitle(_('Schedule'), 'fas fa-list'));
    }

    /**

     * @throws ForbiddenRequestException
     */
    public function titlePersons(): void
    {
        $this->setPageTitle(new PageTitle(_('Whole program'), 'fas fa-list'));
    }

    /**

     * @throws ForbiddenRequestException
     */
    public function titleDetail(): void
    {
        $this->setPageTitle(new PageTitle(\sprintf(_('Schedule items')), 'fas fa-clipboard-list'));
    }

    /**
     *
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
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
    protected function createComponentAllPersonsGrid(): AllPersonsGrid
    {
        return new AllPersonsGrid($this->getContext(), $this->getEvent());
    }

    /**

     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentItemsGrid(): ItemsGrid
    {
        return new ItemsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ServiceScheduleGroup
    {
        return $this->serviceScheduleGroup;
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege

     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }
}
