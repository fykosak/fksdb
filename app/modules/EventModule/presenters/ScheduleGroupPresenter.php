<?php

namespace EventModule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\AllPersonsGrid;
use FKSDB\Components\Grids\Schedule\GroupsGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;

/**
 * Class ScheduleGroupPresenter
 * *
 */
class ScheduleGroupPresenter extends BasePresenter {
    use EventEntityTrait;

    private ServiceScheduleGroup $serviceScheduleGroup;

    public function injectServiceScheduleGroup(ServiceScheduleGroup $serviceScheduleGroup): void {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList(): void {
        $this->setTitle(_('Schedule'), 'fa fa-calendar-check-o');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titlePersons(): void {
        $this->setTitle(_('Whole program'), 'fa fa-calendar-check-o');
    }

    /**
     * @return Control
     * @throws NotImplementedException
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @return Control
     * @throws NotImplementedException
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @return BaseGrid
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentGrid(): BaseGrid {
        return new GroupsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return AllPersonsGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentAllPersonsGrid(): AllPersonsGrid {
        return new AllPersonsGrid($this->getContext(), $this->getEvent());
    }

    protected function getORMService(): ServiceScheduleGroup {
        return $this->serviceScheduleGroup;
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }
}
