<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Fyziklani\SchoolCheckComponent;
use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\Components\Controls\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\TeamApplicationGrid;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationPresenter
 * *
 * @method ModelFyziklaniTeam getEntity()
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;
    
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
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
    public function renderDetail() {
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
     * @return AbstractApplicationGrid
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     */
    protected function createComponentGrid(): AbstractApplicationGrid {
        return new TeamApplicationGrid($this->getEvent(), $this->getHolder(), $this->getContext());
    }

    protected function createComponentTeamRestsControl(): TeamRestsComponent {
        return new TeamRestsComponent($this->getContext());
    }

    protected function getORMService(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }
}
