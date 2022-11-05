<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Fyziklani\SchoolCheckComponent;
use FKSDB\Components\Controls\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\Controls\Transitions\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\Fyziklani\FOFTeamFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\FOLTeamFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\TeamFormComponent;
use FKSDB\Components\Grids\Application\TeamApplicationsGrid;
use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\PageComponent;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Transitions\Machine\FyziklaniTeamMachine;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\MissingServiceException;
use Nette\InvalidStateException;

/**
 * @method TeamModel2 getEntity()
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter
{

    private TeamService2 $teamService;

    final public function injectServiceFyziklaniTeam(TeamService2 $teamService): void
    {
        $this->teamService = $teamService;
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create team'), 'fa fa-calendar-plus');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit team "%s"'), $this->getEntity()->name), 'fa fa-edit');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): void
    {
        $event = $this->getEvent();
        $this->setAuthorized(
            $this->eventAuthorizator->isAllowed($this->getEntity(), 'org-edit', $event) || (
                $event->isRegistrationOpened()
                && $this->eventAuthorizator->isAllowed($this->getEntity(), 'edit', $event)
            )
        );
    }

    public function authorizedCreate(): void
    {
        $event = $this->getEvent();
        $this->setAuthorized(
            $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'org-create', $event) || (
                $event->isRegistrationOpened()
                && $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'create', $event)
            )
        );
    }

    public function requiresLogin(): bool
    {
        return $this->getAction() !== 'create';
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderDetail(): void
    {
        parent::renderDetail();
        try {
            $setup = $this->getEvent()->getFyziklaniGameSetup();
            $rankVisible = $setup->result_hard_display;
        } catch (NotSetGameParametersException $exception) {
            $rankVisible = false;
        }
        $this->template->isOrg = $this->eventAuthorizator->isAllowed(
            $this->getEntity(),
            'org-detail',
            $this->getEvent()
        );
        $this->template->rankVisible = $rankVisible;
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return $this->getEvent()->isTeamEvent();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentSeating(): ProviderComponent
    {
        return new ProviderComponent(
            new PageComponent($this->getContext()),
            [$this->getEntity()],
            $this->getContext()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentSchoolCheck(): SchoolCheckComponent
    {
        return new SchoolCheckComponent($this->getEvent(), $this->getContext());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): TeamFormComponent
    {
        return $this->createTeamForm(null);
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): TeamFormComponent
    {
        return $this->createTeamForm($this->getEntity());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    private function createTeamForm(?Model $model): TeamFormComponent
    {
        switch ($this->getEvent()->event_type_id) {
            case 1:
                return new FOFTeamFormComponent(
                    $this->getMachine(),
                    $this->getEvent(),
                    $this->getContext(),
                    $model
                );
            case 9:
                return new FOLTeamFormComponent(
                    $this->getMachine(),
                    $this->getEvent(),
                    $this->getContext(),
                    $model
                );
        }
        throw new InvalidStateException(_('Event type is not supported'));
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TeamApplicationsGrid
    {
        return new TeamApplicationsGrid($this->getEvent(), $this->getContext());
    }

    protected function createComponentTeamRestsControl(): TeamRestsComponent
    {
        return new TeamRestsComponent($this->getContext());
    }

    protected function getORMService(): TeamService2
    {
        return $this->teamService;
    }

    /**
     * @return FyziklaniTeamMachine
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    private function getMachine(): FyziklaniTeamMachine
    {
        static $machine;
        if (!isset($machine)) {
            try {
                switch ($this->getEvent()->event_type_id) {
                    case 1:
                        $machine = $this->getContext()->getService('fof.default.machine');
                        break;
                    case 9:
                        $machine = $this->getContext()->getService('fol.default.machine');
                        break;
                    default:
                        throw new InvalidStateException();
                }
            } catch (MissingServiceException $exception) {
            }
            if (!$machine instanceof FyziklaniTeamMachine) {
                throw new BadTypeException(FyziklaniTeamMachine::class, $machine);
            }
        }
        return $machine;
    }

    /**
     * @return BaseComponent
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentApplicationTransitions(): BaseComponent
    {
        return new TransitionButtonsComponent(
            $this->getMachine(),
            $this->getContext(),
            $this->getMachine()->createHolder($this->getEntity())
        );
    }
}
