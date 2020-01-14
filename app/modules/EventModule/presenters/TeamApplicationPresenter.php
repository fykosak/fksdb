<?php

namespace EventModule;

use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Components\Grids\Events\Application\TeamApplicationGrid;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class ApplicationPresenter
 * @package EventModule
 */
class TeamApplicationPresenter extends AbstractApplicationPresenter {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    public function titleList() {
        $this->setTitle(_('List of team applications'));
        $this->setIcon('fa fa-users');
    }

    public function titleDetail() {
        $this->setTitle(_('Team application detail'));
        $this->setIcon('fa fa-user');
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDetail() {
        if ($this->isTeamEvent()) {
            parent::authorizedDetail();
        } else {
            $this->setAuthorized(false);
        }
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedList() {
        if ($this->isTeamEvent()) {
            parent::authorizedList();
        } else {
            $this->setAuthorized(false);
        }
    }

    /**
     * @return ApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGrid(): AbstractApplicationGrid {
        return new TeamApplicationGrid($this->getEvent(), $this->getTableReflectionFactory());
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
    }

    /**
     * @return AbstractServiceSingle
     */
    function getORMService() {
        return $this->serviceFyziklaniTeam;
    }

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return 'fyziklani.team';
    }
}
