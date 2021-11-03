<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\ScheduleItemFormContainer;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method ModelScheduleItem getEntity()
 */
class ScheduleItemPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private ModelScheduleGroup $group;

    private ServiceScheduleItem $serviceScheduleItem;

    final public function injectServiceScheduleItem(ServiceScheduleItem $serviceScheduleItem): void
    {
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(\sprintf(_('Schedule item "%s"'), $this->getEntity()->getLabel()), 'fas fa-clipboard');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(\sprintf(_('Edit schedule item "%s"'), $this->getEntity()->getLabel()), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create schedule item'), 'fa fa-plus');
    }

    /**
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
    protected function createComponentCreateForm(): ScheduleItemFormContainer
    {
        return new ScheduleItemFormContainer($this->getEvent(), $this->getContext(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentEditForm(): ScheduleItemFormContainer
    {
        return new ScheduleItemFormContainer($this->getEvent(), $this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentPersonsGrid(): PersonsGrid
    {
        return new PersonsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ServiceScheduleItem
    {
        return $this->serviceScheduleItem;
    }

    /**
     * @param string|Resource $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    protected function createComponentGrid(): BaseGrid
    {
        throw new NotImplementedException();
    }
}
