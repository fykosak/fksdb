<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\ApplicationComponent;
use FKSDB\Components\Controls\Events\TransitionButtonsComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\PersonGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Services\EventParticipantService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
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
            sprintf(_('Application detail "%s"'), $this->getEntity()->__toString()),
            'fa fa-user'
        );
    }

    final public function titleTransitions(): PageTitle
    {
        return new PageTitle(null, _('Group transitions'), 'fa fa-exchange-alt');
    }

    /**
     * @throws EventNotFoundException
     */
    public function renderDetail(): void
    {
        $this->getTemplate()->event = $this->getEvent();
        $this->getTemplate()->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderList(): void
    {
        $this->getTemplate()->event = $this->getEvent();
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
     * @throws NeonSchemaException
     * @throws \ReflectionException
     */
    public function getHolder(): BaseHolder
    {
        $holder = $this->getDummyHolder();
        $holder->setModel($this->getEntity());
        return $holder;
    }

    protected function createComponentPersonScheduleGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentApplicationComponent(): ApplicationComponent
    {
        return new ApplicationComponent(
            $this->getContext(),
            new ApplicationHandler($this->getEvent(), new MemoryLogger(), $this->getContext()),
            $this->getHolder()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentApplicationTransitions(): BaseComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            new ApplicationHandler($this->getEvent(), new MemoryLogger(), $this->getContext()),
            $this->getHolder()
        );
    }

    abstract protected function createComponentGrid(): BaseGrid;

    /**
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }
}
