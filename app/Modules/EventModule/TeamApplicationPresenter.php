<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Entity\TeamApplicationFormComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\SchoolCheckComponent;
use FKSDB\Components\Controls\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\Controls\Transitions\TransitionButtonsComponent;
use FKSDB\Components\Grids\Application\AbstractApplicationsGrid;
use FKSDB\Components\Grids\Application\TeamApplicationsGrid;
use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\PageComponent;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Transitions\Machine\FyziklaniTeamMachine;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\MissingServiceException;

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

    protected function createComponentCreateTeamApplicationForm(): TeamApplicationFormComponent {
        return new TeamApplicationFormComponent($this->getContext(), null);
    }

    /**
     * @return TeamApplicationFormComponent
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentEditTeamApplicationForm(): TeamApplicationFormComponent {
        return new TeamApplicationFormComponent($this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     */
    protected function createComponentGrid(): AbstractApplicationsGrid
    {
        return new TeamApplicationsGrid($this->getEvent(), $this->getHolder(), $this->getContext());
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
                $machine = $this->getContext()->getService(
                    sprintf('fyziklani%dteam.machine', $this->getEvent()->event_year)
                );
            } catch (MissingServiceException $exception) {
                $machine = $this->getContext()->getService('fyziklani.default.machine');
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
