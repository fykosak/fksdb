<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Schedule\Attendance\CodeAttendance;
use FKSDB\Components\Schedule\Forms\ScheduleItemForm;
use FKSDB\Components\Schedule\PersonGrid;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;

final class ItemPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<ScheduleItemModel> */
    use EntityPresenterTrait;

    /** @persistent */
    public ?int $groupId = null;

    /**
     * @throws EventNotFoundException
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(ScheduleItemModel::RESOURCE_ID, $this->getEvent()),
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
     * @throws EventNotFoundException
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEntity()),
            'detail',
            $this->getEvent()
        );
    }
    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(
                _('%s of %s '),
                $this->getEntity()->name->get($this->translator->lang),
                $this->getEntity()->schedule_group->getName()->get($this->translator->lang)
            ),
            'fas fa-clipboard'
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws EventNotFoundException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEntity()),
            'edit',
            $this->getEvent()
        );
    }
    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit item "%s"'), $this->getEntity()->name->get($this->translator->lang)),
            'fas fa-pen'
        );
    }

    /**
     * @throws GoneException
     */
    protected function getORMService(): ScheduleItemService
    {
        throw new GoneException();
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    private function getGroup(): ScheduleGroupModel
    {
        /** @var ScheduleGroupModel|null $candidate */
        $candidate = $this->getEvent()->getScheduleGroups()->where('schedule_group_id', $this->groupId)->fetch();
        if ($candidate) {
            return $candidate;
        } else {
            throw new NotFoundException(_('Model does not exist.'));
        }
    }

    protected function loadModel(): ScheduleItemModel
    {
        /** @var ScheduleItemModel|null $candidate */
        $candidate = $this->getGroup()->getItems()->where('schedule_item_id', $this->id)->fetch();
        if ($candidate) {
            return $candidate;
        } else {
            throw new NotFoundException(_('Model does not exist.'));
        }
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    protected function createComponentCreateForm(): ScheduleItemForm
    {
        return new ScheduleItemForm($this->getGroup(), $this->getContext(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    protected function createComponentEditForm(): ScheduleItemForm
    {
        return new ScheduleItemForm($this->getGroup(), $this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    protected function createComponentPersonsGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NotFoundException
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
