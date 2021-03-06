<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\ScheduleItemFormContainer;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

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
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Schedule item "%s"'), $this->getEntity()->getLabel()), 'fas fa-clipboard'));
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Edit schedule item "%s"'), $this->getEntity()->getLabel()), 'fas fa-pen'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('Create schedule item'), 'fa fa-plus'));
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
     * @return ScheduleItemFormContainer
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): ScheduleItemFormContainer {
        return new ScheduleItemFormContainer($this->getEvent(), $this->getContext(), null);
    }

    /**
     * @return ScheduleItemFormContainer
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentEditForm(): ScheduleItemFormContainer {
        return new ScheduleItemFormContainer($this->getEvent(), $this->getContext(), $this->getEntity());
    }

    /**
     * @return PersonsGrid
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentPersonsGrid(): PersonsGrid {
        return new PersonsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ServiceScheduleItem {
        return $this->serviceScheduleItem;
    }

    /**
     * @param string|Resource $resource
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
