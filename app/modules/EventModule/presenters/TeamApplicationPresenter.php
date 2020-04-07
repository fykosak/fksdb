<?php

namespace EventModule;

use FKSDB\Components\Controls\Fyziklani\SchoolCheckControl;
use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\Components\Controls\Schedule\Rests\TeamRestsControl;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Components\Grids\Events\Application\TeamApplicationGrid;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class ApplicationPresenter
 * @package EventModule
 * @method ModelFyziklaniTeam getEntity()
 * @method ModelFyziklaniTeam loadEntity(int $id)
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter {
    /** @var ServiceFyziklaniTeam */
    private $serviceFyziklaniTeam;

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
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
     * @throws BadRequestException

     * @throws AbortException
     * @throws BadRequestException
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
        $this->template->model = $this->getEntity();
    }

    /**
     * @return SeatingControl
     */
    protected function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->getContext());
    }

    /**
     * @return SchoolCheckControl
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentSchoolCheck(): SchoolCheckControl {
        return new SchoolCheckControl($this->getEvent(), $this->getAcYear(), $this->getContext());
    }

    /**
     * @return ApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentGrid(): AbstractApplicationGrid {
        return new TeamApplicationGrid($this->getEvent(), $this->getHolder(), $this->getContext());
    }

    /**
     * @return TeamRestsControl
     */
    protected function createComponentTeamRestsControl(): TeamRestsControl {
        return new TeamRestsControl($this->getContext());
    }

    /**
     * @return AbstractServiceSingle|ServiceFyziklaniTeam
     */
    protected function getORMService(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }
}
