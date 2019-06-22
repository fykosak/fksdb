<?php

namespace EventModule;

use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\TeamApplicationGrid;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

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
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDetail() {
        if ($this->isTeamEvent()) {
            $this->setAuthorized($this->eventIsAllowed('event.application', 'detail'));
        } else {
            $this->setAuthorized(false);
        }
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedList() {
        if ($this->isTeamEvent()) {
            $this->setAuthorized($this->eventIsAllowed('event.application', 'list'));
        } else {
            $this->setAuthorized(false);
        }
    }

    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function loadModel(int $id) {
        $row = $this->serviceFyziklaniTeam->findByPrimary($id);
        if (!$row) {
            throw new BadRequestException('Model not found');
        }
        $model = ModelFyziklaniTeam::createFromActiveRow($row);
        if ($model->event_id != $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }
        $this->model = $model;
    }

    /**
     * @return \FKSDB\Components\Grids\Events\Application\ApplicationGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentGrid(): AbstractApplicationGrid {
        return new TeamApplicationGrid($this->getEvent(), $this->getTableReflectionFactory());
    }

    /**
     * @return ModelFyziklaniTeam
     */
    protected function getModel(): ModelFyziklaniTeam {
        return $this->model;
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function renderDetail() {
        $this->template->acYear = $this->getAcYear();
        try {
            $setup = $this->getEvent()->getFyziklaniGameSetup();
            $rankVisible = $setup->result_hard_display;
        } catch (NotSetGameParametersException $exception) {
            $rankVisible = false;
        }
        $this->template->rankVisible = $rankVisible;
        $this->template->model = $this->getModel();
    }
}
