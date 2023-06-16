<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\ApplicationComponent;
use FKSDB\Components\Controls\Transition\FastTransitionComponent;
use FKSDB\Components\Controls\Transition\MassTransitionsComponent;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\Schedule\PersonGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

abstract class AbstractApplicationPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    protected EventParticipantService $eventParticipantService;

    final public function injectServiceEventParticipant(EventParticipantService $eventParticipantService): void
    {
        $this->eventParticipantService = $eventParticipantService;
    }

    final public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of applications'), 'fas fa-address-book');
    }

    final public function titleTransition(): PageTitle
    {
        return new PageTitle(null, _('Fast attendance'), 'fas fa-fast-forward');
    }

    final public function titleTransitions(): PageTitle
    {
        return new PageTitle(null, _('Group transitions'), 'fa fa-exchange-alt');
    }


    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws \Throwable
     */
    final public function titleDetail(): PageTitle
    {
        $entity = $this->getEntity();
        if ($entity instanceof TeamModel2) {
            return new PageTitle(
                null,
                sprintf(_('Application detail "%s"'), $entity->name),
                'fa fa-user'
            );
        }
        return new PageTitle(
            null,
            sprintf(_('Application detail "%s"'), $entity->__toString()),
            'fa fa-user'
        );
    }

    /**
     * @throws EventNotFoundException
     */
    public function renderDetail(): void
    {
        $this->template->event = $this->getEvent();
        $this->template->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
        $this->template->isOrg = $this->isAllowed('event.application', 'default');
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderList(): void
    {
        $this->template->event = $this->getEvent();
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    public function getHolder(): ModelHolder
    {
        return $this->getMachine()->createHolder($this->getEntity());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function getMachine(): Machine
    {
        return $this->eventDispatchFactory->getEventMachine($this->getEvent());
    }

    protected function createComponentPersonScheduleGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    protected function createComponentApplicationComponent(): ApplicationComponent
    {
        return new ApplicationComponent(
            $this->getContext(),
            $this->getHolder()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    protected function createComponentApplicationTransitions(): BaseComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getEvent(),
            $this->getHolder()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    final protected function createComponentMassTransitions(): MassTransitionsComponent
    {
        return new MassTransitionsComponent($this->getContext(), $this->getEvent());
    }

    abstract protected function createComponentFastTransition(): FastTransitionComponent;
}
