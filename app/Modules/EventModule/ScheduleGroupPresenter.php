<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Entity\ScheduleGroupFormComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\AllPersonsGrid;
use FKSDB\Components\Grids\Schedule\GroupsGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * Class ScheduleGroupPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelScheduleGroup getEntity()
 */
class ScheduleGroupPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    private ServiceScheduleGroup $serviceScheduleGroup;

    final public function injectServiceScheduleGroup(ServiceScheduleGroup $serviceScheduleGroup): void {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Schedule'), 'fas fa-calendar'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titlePersons(): void {
        $this->setPageTitle(new PageTitle(_('Whole program'), 'fas fa-calendar'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Schedule items')), 'fas fa-calendar'));
    }

    /**
     *
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    final public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    /**
     * @return ScheduleGroupFormComponent
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): ScheduleGroupFormComponent {
        return new ScheduleGroupFormComponent($this->getEvent(), $this->getContext(), null);
    }

    /**
     * @return ScheduleGroupFormComponent
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentEditForm(): ScheduleGroupFormComponent {
        return new ScheduleGroupFormComponent($this->getEvent(), $this->getContext(), $this->getEntity());
    }

    /**
     * @return BaseGrid
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): BaseGrid {
        return new GroupsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return AllPersonsGrid
     * @throws EventNotFoundException
     */
    protected function createComponentAllPersonsGrid(): AllPersonsGrid {
        return new AllPersonsGrid($this->getContext(), $this->getEvent());
    }

    /**
     * @return ItemsGrid
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentItemsGrid(): ItemsGrid {
        return new ItemsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ServiceScheduleGroup {
        return $this->serviceScheduleGroup;
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }
}
