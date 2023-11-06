<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\Schedule\AllPersonList;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class PersonPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PersonScheduleModel> */
    use EntityPresenterTrait;

    private const FILTERED_TYPES = [
        ScheduleGroupType::ACCOMMODATION_GENDER,
        ScheduleGroupType::ACCOMMODATION_TEACHER,
        ScheduleGroupType::VISA,
        ScheduleGroupType::APPAREL,
        ScheduleGroupType::TRANSPORT,
        ScheduleGroupType::TICKET,
    ];

    private PersonScheduleService $service;

    public function inject(PersonScheduleService $service): void
    {
        $this->service = $service;
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedMySchedule(): bool
    {
        $person = $this->getLoggedPerson();
        return $person && count($person->getEventRoles($this->getEvent()));
    }

    /**
     * @throws EventNotFoundException
     */
    public function renderMySchedule(): void
    {
        $this->template->schedule = $this->prepareSchedule();
        $this->template->person = $this->getLoggedPerson();
    }

    public function titleMySchedule(): PageTitle
    {
        return new PageTitle(null, _('My schedule'), 'fas fa-list');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws EventNotFoundException
     */
    public function renderDetail(): void
    {
        $this->template->schedule = $this->prepareSchedule();
        $this->template->otherSchedule = $this->getEvent()
            ->getScheduleGroups()
            ->where('schedule_group_type', self::FILTERED_TYPES);
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    public function titleDetail(): PageTitle
    {
        $model = $this->getEntity();
        return new PageTitle(
            null,
            sprintf(
                _('%s@%s: %s'),
                $model->schedule_item->name->getText($this->translator->lang),
                $model->schedule_item->schedule_group->name->getText($this->translator->lang),
                $model->person->getFullName()
            ),
            'fas fa-list'
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Schedule'), 'fas fa-list');
    }

    /**
     * @throws EventNotFoundException
     * @phpstan-return ScheduleGroupModel[][]
     */
    private function prepareSchedule(): array
    {
        $dates = [];
        /** @var ScheduleGroupModel $group $group */
        foreach ($this->getEvent()->getScheduleGroups()->order('schedule_group.start') as $group) {
            if (in_array($group->schedule_group_type->value, self::FILTERED_TYPES)) {
                continue;
            }
            $currentKey = $group->start->format('Y-d-m');
            $dates[$currentKey] = $dates[$currentKey] ?? [];
            $dates[$currentKey][] = $group;
        }
        return $dates;
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->eventAuthorizator->isAllowed('event.schedule.person', $privilege, $this->getEvent());
    }

    protected function getORMService(): PersonScheduleService
    {
        return $this->service;
    }

    /**
     * @phpstan-return TransitionButtonsComponent<PersonScheduleModel>
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->eventDispatchFactory->getPersonScheduleMachine(), // @phpstan-ignore-line
            $this->getEntity()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): AllPersonList
    {
        return new AllPersonList($this->getContext(), $this->getEvent());
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }
}
