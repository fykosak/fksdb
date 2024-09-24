<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Schedule\Attendance\CodeAttendance;
use FKSDB\Components\Schedule\Forms\ScheduleItemForm;
use FKSDB\Components\Schedule\PersonGrid;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class ItemPresenter extends BasePresenter
{
    /** @phpstan-use EventEntityPresenterTrait<ScheduleItemModel> */
    use EventEntityPresenterTrait;

    private ScheduleItemService $service;

    /** @persistent */
    public ?int $groupId = null;

    final public function injectService(ScheduleItemService $service): void
    {
        $this->service = $service;
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource(ScheduleItemModel::RESOURCE_ID, $this->getEvent()),
            'create',
            $this->getEvent()
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create item'), 'fas fa-plus');
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws ForbiddenRequestException
     * @throws EventNotFoundException
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedEvent($this->getEntity(), 'detail', $this->getEvent());
    }
    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
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
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(
                _('%s of %s '),
                $this->getEntity()->name->getText($this->translator->lang),
                $this->getEntity()->schedule_group->name->getText($this->translator->lang)
            ),
            'fas fa-clipboard'
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     * @throws ForbiddenRequestException
     * @throws EventNotFoundException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedEvent($this->getEntity(), 'edit', $this->getEvent());
    }
    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit item "%s"'), $this->getEntity()->name->getText($this->translator->lang)),
            'fas fa-pen'
        );
    }


    protected function getORMService(): ScheduleItemService
    {
        return $this->service;
    }

    /**
     * @throws EventNotFoundException
     */
    private function getGroup(): ?ScheduleGroupModel
    {
        if (!$this->groupId) {
            return null;
        }
        /** @var ScheduleGroupModel|null $group */
        $group = $this->getEvent()->getScheduleGroups()->where('schedule_group_id', $this->groupId)->fetch();
        return $group;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): ScheduleItemForm
    {
        return new ScheduleItemForm($this->getGroup(), $this->getContext(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): ScheduleItemForm
    {
        return new ScheduleItemForm($this->getGroup(), $this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentPersonsGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentCode(): CodeAttendance
    {
        return new CodeAttendance(
            $this->getContext(),
            $this->getEntity(),
            $this->eventDispatchFactory->getPersonScheduleMachine()
        );
    }
}
