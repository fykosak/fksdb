<?php

namespace FyziklaniModule;

use EventModule\EventEntityTrait;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamControl;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\Components\Grids\Fyziklani\TeamSubmitsGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;
use function sprintf;

/**
 * Class ClosePresenter
 * @package FyziklaniModule
 * @property FormControl closeCategoryAForm
 * @method ModelFyziklaniTeam getEntity()
 */
class ClosePresenter extends BasePresenter {

    use EventEntityTrait;

    /* ******* TITLE ***********/
    public function titleList() {
        $this->setTitle(_('Uzavírání bodování'));
        $this->setIcon('fa fa-check');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleTeam(int $id) {
        $this->setTitle(sprintf(_('Uzavírání bodování týmu "%s"'), $this->loadEntity($id)->name));
        $this->setIcon('fa fa-check-square-o');
    }

    public function titleHard() {
        $this->setTitle(_('Hard close submitting'));
        $this->setIcon('fa fa-check');
    }

    /* ******* authorized methods ***********/
    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedTeam() {
        $this->setAuthorized($this->isAllowedForEventOrg($this->getModelResource(), 'team'));
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedList() {
        $this->setAuthorized($this->isAllowedForEventOrg($this->getModelResource(), 'team'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizeHard() {
        $this->setAuthorized($this->isAllowedForEventOrg($this->getModelResource(), 'hard'));
    }
    /* *********** ACTIONS **************** */
    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionTeam(int $id) {
        $team = $this->loadEntity($id);
        try {
            $team->canClose();
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage());
            $this->redirect('list');
        }
        $this->actionHard($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionHard(int $id) {
        $team = $this->loadEntity($id);
        $control = $this->getComponent('closeTeamControl');
        if (!$control instanceof CloseTeamControl) {
            throw new BadRequestException();
        }
        $control->setTeam($team);
    }

    /* ********* COMPONENTS ************* */

    /**
     * @return CloseTeamControl
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentCloseTeamControl(): CloseTeamControl {
        return new CloseTeamControl($this->getEvent(), $this->translator, $this->getServiceFyziklaniTask());
    }

    /**
     * @return CloseTeamsGrid
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentCloseTeamsGrid(): CloseTeamsGrid {
        return new CloseTeamsGrid($this->getEvent(), $this->getServiceFyziklaniTeam(), $this->getTableReflectionFactory());
    }

    /**
     * @return TeamSubmitsGrid
     */
    protected function createComponentTeamSubmitsGrid(): TeamSubmitsGrid {
        return new TeamSubmitsGrid($this->getEntity(), $this->getServiceFyziklaniSubmit(), $this->getTableReflectionFactory());
    }


    /**
     * @inheritDoc
     */
    protected function getORMService() {
        return $this->getServiceFyziklaniTeam();
    }

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return 'fyziklani.close';
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function isAllowed($resource, string $privilege): bool {
        return $this->isAllowedForEventOrg($resource, $privilege);
    }
}
