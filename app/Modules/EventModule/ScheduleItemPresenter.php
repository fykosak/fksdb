<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\ScheduleItemFormContainer;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method ScheduleItemModel getEntity()
 */
class ScheduleItemPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private ScheduleGroupModel $group;

    private ScheduleItemService $scheduleItemService;

    final public function injectServiceScheduleItem(ScheduleItemService $scheduleItemService): void
    {
        $this->scheduleItemService = $scheduleItemService;
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Schedule item "%s"'), $this->getEntity()->getLabel()),
            'fas fa-clipboard'
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit schedule item "%s"'), $this->getEntity()->getLabel()),
            'fas fa-pen'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create schedule item'), 'fa fa-plus');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
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
     * @throws GoneException
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
     * @throws GoneException
     */
    protected function createComponentPersonsGrid(): PersonsGrid
    {
        return new PersonsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ScheduleItemService
    {
        return $this->scheduleItemService;
    }

    /**
     * @param string|Resource $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    protected function createComponentGrid(): BaseGrid
    {
        throw new NotImplementedException();
    }
}
