<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Entity\ScheduleItemFormContainer;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class ScheduleItemPresenter
 * @method ModelScheduleItem getEntity()
 */
class ScheduleItemPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    private ModelScheduleGroup $group;

    private ServiceScheduleItem $serviceScheduleItem;

    final public function injectServiceScheduleItem(ServiceScheduleItem $serviceScheduleItem): void {
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Schedule item "%s"'), $this->getEntity()->getLabel()), 'fa fa-calendar-check-o'));
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Edit schedule item "%s"'), $this->getEntity()->getLabel()), 'fa fa-calendar-check-o'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('Create schedule item'), 'fa fa-calendar-check-o'));
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
     * @return ScheduleItemFormContainer
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): ScheduleItemFormContainer {
        return new ScheduleItemFormContainer($this->getEvent(), $this->getContext(), null);
    }

    /**
     * @return ScheduleItemFormContainer
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): ScheduleItemFormContainer {
        return new ScheduleItemFormContainer($this->getEvent(), $this->getContext(), $this->getEntity());
    }

    /**
     * @return PersonsGrid
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    protected function createComponentPersonsGrid(): PersonsGrid {
        return new PersonsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ServiceScheduleItem {
        return $this->serviceScheduleItem;
    }

    /**
     * @param string|IResource $resource
     * @param string|null $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    protected function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
    }
}
