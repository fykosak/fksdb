<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Fyziklani\SchoolCheckComponent;
use FKSDB\Components\Controls\Fyziklani\Seating\SeatingControl;
use FKSDB\Components\Controls\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\Grids\Application\AbstractApplicationsGrid;
use FKSDB\Components\Grids\Application\TeamApplicationsGrid;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationPresenter
 * *
 * @method ModelFyziklaniTeam getEntity()
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    final public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @return bool
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool {
        return $this->isTeamEvent();
    }

    /**
     * @return void
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws BadTypeException
     */
    public function renderDetail(): void {
        parent::renderDetail();
        $this->template->acYear = $this->getAcYear();
        try {
            $setup = $this->getEvent()->getFyziklaniGameSetup();
            $rankVisible = $setup->result_hard_display;
        } catch (NotSetGameParametersException $exception) {
            $rankVisible = false;
        }
        $this->template->rankVisible = $rankVisible;
        $this->template->model = $this->getEntity();
    }

    protected function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->getContext());
    }

    /**
     * @return SchoolCheckComponent
     * @throws EventNotFoundException
     */
    protected function createComponentSchoolCheck(): SchoolCheckComponent {
        return new SchoolCheckComponent($this->getEvent(), $this->getAcYear(), $this->getContext());
    }

    /**
     * @return AbstractApplicationsGrid
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     */
    protected function createComponentGrid(): AbstractApplicationsGrid {
        return new TeamApplicationsGrid($this->getEvent(), $this->getHolder(), $this->getContext());
    }

    protected function createComponentTeamRestsControl(): TeamRestsComponent {
        return new TeamRestsComponent($this->getContext());
    }

    protected function getORMService(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }
}
