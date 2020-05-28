<?php

namespace EventModule;

use FKSDB\Components\Controls\Fyziklani\SchoolCheckComponent;
use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\Components\Controls\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Components\Grids\Events\Application\TeamApplicationGrid;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationPresenter
 * *
 * @method ModelFyziklaniTeam getEntity()
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter {
    /** @var ServiceFyziklaniTeam */
    private $serviceFyziklaniTeam;

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @return void
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    protected function isEnabled(): bool {
        return $this->isTeamEvent();
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderDetail(int $id) {
        parent::renderDetail($id);
        $this->template->acYear = $this->getAcYear();
        try {
            $setup = $this->getEvent()->getFyziklaniGameSetup();
            $rankVisible = $setup->result_hard_display;
        } catch (NotSetGameParametersException $exception) {
            $rankVisible = false;
        }
        $this->template->rankVisible = $rankVisible;
        $this->template->model = $this->loadEntity($id);
    }

    protected function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->getContext());
    }

    /**
     * @return SchoolCheckComponent
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentSchoolCheck(): SchoolCheckComponent {
        return new SchoolCheckComponent($this->getEvent(), $this->getAcYear(), $this->getContext());
    }

    /**
     * @return ApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    protected function createComponentGrid(): AbstractApplicationGrid {
        return new TeamApplicationGrid($this->getEvent(), $this->getHolder(), $this->getContext());
    }

    protected function createComponentTeamRestsControl(): TeamRestsComponent {
        return new TeamRestsComponent($this->getContext());
    }

    /**
     * @return AbstractServiceSingle|ServiceFyziklaniTeam
     */
    protected function getORMService(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }
}
