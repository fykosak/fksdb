<?php

namespace EventModule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Events\ParticipantsGrid;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\NotImplementedException;

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

    protected function startup() {
        parent::startup();
        if (!\in_array($this->getEvent()->event_type_id, [1, 9])) {
            $this->flashMessage(_('Thi GUI don\'t works for single applications.'), self::FLASH_INFO);
        }
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDetail() {
        // TODO teamApplication
        $this->setAuthorized($this->eventIsAllowed('event.application', 'detail'));
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->eventIsAllowed('event.application', 'list'));
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
        $model = ModelFyziklaniTeam::createFromTableRow($row);
        if ($model->event_id != $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }
        $this->model = $model;
    }

    /**
     * @return ParticipantsGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
        // return new TeamGrid($this->getEvent());
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
