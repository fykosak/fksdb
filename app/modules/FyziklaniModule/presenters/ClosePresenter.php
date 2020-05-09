<?php

namespace FyziklaniModule;

use EventModule\EventEntityTrait;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\Components\Grids\Fyziklani\TeamSubmitsGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;

/**
 * Class ClosePresenter
 * @package FyziklaniModule
 * @property FormControl closeCategoryAForm
 * @method ModelFyziklaniTeam getEntity()
 * @method ModelFyziklaniTeam loadEntity(int $id)
 */
class ClosePresenter extends BasePresenter {

    use EventEntityTrait;

    /* ******* TITLE ***********/
    public function titleList() {
        $this->setTitle(_('Uzavírání bodování'), 'fa fa-check');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleTeam(int $id) {
        $this->setTitle(\sprintf(_('Uzavírání bodování týmu "%s"'), $this->loadEntity($id)->name), 'fa fa-check-square-o');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleHard(int $id) {
        $this->titleTeam($id);
    }

    /* ******* authorized methods ***********/
    /**
     * @throws BadRequestException
     */
    public function authorizedTeam() {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized($this->getModelResource(), 'team'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizeHard() {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized($this->getModelResource(), 'hard'));
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isEventOrContestOrgAuthorized($resource, $privilege);
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
            throw new BadTypeException(CloseTeamControl::class, $control);
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
        return new CloseTeamControl($this->getContext(), $this->getEvent());
    }

    /**
     * @return TeamSubmitsGrid
     */
    protected function createComponentTeamSubmitsGrid(): TeamSubmitsGrid {
        return new TeamSubmitsGrid($this->getEntity(), $this->getContext());
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
     * @return BaseGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGrid(): BaseGrid {
        return new CloseTeamsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @inheritDoc
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException;
    }

    /**
     * @inheritDoc
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException;
    }

}
