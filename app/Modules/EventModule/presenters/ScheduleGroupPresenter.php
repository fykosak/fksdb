<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Entity\ScheduleGroupFormComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\AllPersonsGrid;
use FKSDB\Components\Grids\Schedule\GroupsGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class ScheduleGroupPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ScheduleGroupPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    private ServiceScheduleGroup $serviceScheduleGroup;

    final public function injectServiceScheduleGroup(ServiceScheduleGroup $serviceScheduleGroup): void {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Schedule'), 'fa fa-calendar-check-o'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titlePersons(): void {
        $this->setPageTitle(new PageTitle(_('Whole program'), 'fa fa-calendar-check-o'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Schedule items')), 'fa fa-calendar-check-o'));
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     * @throws BadTypeException
     */
    public function actionEdit(): void {
        $this->traitActionEdit();
    }

    /**
     *
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    /**
     * @return ScheduleGroupFormComponent
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): ScheduleGroupFormComponent {
        return new ScheduleGroupFormComponent($this->getEvent(), $this->getContext(), true);
    }

    /**
     * @return ScheduleGroupFormComponent
     * @throws EventNotFoundException
     */
    protected function createComponentEditForm(): ScheduleGroupFormComponent {
        return new ScheduleGroupFormComponent($this->getEvent(), $this->getContext(), false);
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
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentItemsGrid(): ItemsGrid {
        return new ItemsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ServiceScheduleGroup {
        return $this->serviceScheduleGroup;
    }

    /**
     * @param IResource|string|null $resource
     * @param string $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }
}
