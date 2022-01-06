<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Fyziklani\SchoolCheckComponent;
use FKSDB\Components\Controls\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\Grids\Application\AbstractApplicationsGrid;
use FKSDB\Components\Grids\Application\TeamApplicationsGrid;
use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\PageComponent;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;

/**
 * @method ModelFyziklaniTeam getEntity()
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter
{

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    final public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam): void
    {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
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
        return $this->isTeamEvent();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
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

    protected function getORMService(): ServiceFyziklaniTeam
    {
        return $this->serviceFyziklaniTeam;
    }
}
