<?php

namespace EventModule;

use FKSDB\Components\Controls\Fyziklani\SchoolCheckControl;
use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Components\Grids\Events\Application\TeamApplicationGrid;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Tracy\Debugger;

/**
 * Class ApplicationPresenter
 * @package EventModule
 * @method ModelFyziklaniTeam getEntity()
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     */
    public function injectServiceFyziklaniTeamPosition(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition) {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    protected function isEnabled(): bool {
        return $this->isTeamEvent();
    }

    /**
     * @return ApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGrid(): AbstractApplicationGrid {
        return new TeamApplicationGrid($this->getEvent(), $this->getHolder(), $this->getContext());
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
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
        $this->template->toPay = $this->getEntity()->getScheduleRest();
    }

    /**
     * @return SeatingControl
     */
    public function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->getContext());
    }

    /**
     * @return SchoolCheckControl
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentSchoolCheck(): SchoolCheckControl {
        return new SchoolCheckControl($this->getEvent(), $this->getAcYear(), $this->getContext());
    }

    /**
     * @return AbstractServiceSingle|ServiceFyziklaniTeam
     */
    function getORMService(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }
}
