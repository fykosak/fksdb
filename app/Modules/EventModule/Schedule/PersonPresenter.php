<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Schedule\PersonScheduleList;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\EventModule\BasePresenter;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class PersonPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PersonModel> */
    use EntityPresenterTrait;

    private const FILTERED_TYPES = [
        ScheduleGroupType::ACCOMMODATION_GENDER,
        ScheduleGroupType::ACCOMMODATION_TEACHER,
        ScheduleGroupType::VISA,
        ScheduleGroupType::APPAREL,
        ScheduleGroupType::TRANSPORT,
        ScheduleGroupType::TICKET,
    ];

    private PersonService $personService;

    public function inject(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        $person = $this->getLoggedPerson();
        return $person && count($person->getEventRoles($this->getEvent()));
    }

    /**
     * @throws EventNotFoundException
     */
    public function renderDefault(): void
    {
        $this->template->schedule = $this->prepareSchedule();
        $this->template->person = $this->getLoggedPerson();
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My schedule'), 'fas fa-list');
    }

    /**
     * @throws EventNotFoundException
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function renderDetail(): void
    {
        $this->template->schedule = $this->prepareSchedule();
        $this->template->person = $this->getEntity();
        $this->template->otherSchedule = $this->getEvent()
            ->getScheduleGroups()
            ->where('schedule_group_type', self::FILTERED_TYPES);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Schedule of %s'), $this->getEntity()->getFullName()), 'fas fa-list');
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
        foreach ($this->getEvent()->getScheduleGroups() as $group) {
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

    protected function getORMService(): PersonService
    {
        return $this->personService;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): PersonScheduleList
    {
        return new PersonScheduleList($this->getContext(), $this->getEvent());
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
