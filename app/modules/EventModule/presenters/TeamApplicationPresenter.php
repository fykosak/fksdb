<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Components\Grids\Events\Application\TeamApplicationGrid;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\NotImplementedException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

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

    public function titleList() {
        $this->setTitle(_('List of team applications'));
        $this->setIcon('fa fa-users');
    }

    public function titleDetail() {
        $this->setTitle(_('Team application detail'));
        $this->setIcon('fa fa-user');
    }

    /**
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function isEnabledForEvent(): bool {
        return $this->isTeamEvent();
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
        $this->template->toPay = $this->getEntity()->getScheduleRest();
    }

    /**
     * @return SeatingControl
     */
    public function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->serviceFyziklaniTeamPosition, $this->getTranslator());
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
        return ModelFyziklaniTeam::RESOURCE_ID;
    }

    /**
     * @inheritDoc
     */
    protected function getCreateForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function getEditForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function handleCreateFormSuccess(Form $form) {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function handleEditFormSuccess(Form $form) {
        throw new NotImplementedException();
    }
}
