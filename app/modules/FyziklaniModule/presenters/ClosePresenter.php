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

    public function titleTeam() {
        $this->setTitle(sprintf(_('Uzavírání bodování týmu "%s"'), $this->getEntity()->name));
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
        $this->setAuthorized($this->eventIsAllowed($this->getModelResource(), 'team'));
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedList() {
        $this->setAuthorized($this->eventIsAllowed($this->getModelResource(), 'team'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizeHard() {
        $this->setAuthorized($this->eventIsAllowed($this->getModelResource(), 'hard'));
    }
    /* *********** ACTIONS **************** */
    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionTeam() {
        $team = $this->getEntity();
        if (!$team->hasOpenSubmitting()) {
            $this->flashMessage(_('Team má uzatvorené bodovanie'));
            $this->redirect('list');

        } elseif (!$team->hasAllSubmitsChecked()) {
            $this->flashMessage(_('Team nemá checknuté všetky úlohy'));
            $this->redirect('list');
        }
        $control = $this->getComponent('closeTeamControl');
        if (!$control instanceof CloseTeamControl) {
            throw new BadRequestException();
        }
        $control->setTeam($this->getEntity());
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionHard() {
        $control = $this->getComponent('closeTeamControl');
        if (!$control instanceof CloseTeamControl) {
            throw new BadRequestException();
        }
        $control->setTeam($this->getEntity());
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
     * @throws AbortException
     * @throws BadRequestException
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
}
