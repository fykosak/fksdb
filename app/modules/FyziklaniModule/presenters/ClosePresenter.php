<?php

namespace FyziklaniModule;

use EventModule\EventEntityTrait;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamControl;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\Components\Grids\Fyziklani\TeamSubmitsGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use function sprintf;

/**
 * Class ClosePresenter
 * @package FyziklaniModule
 * @property FormControl closeCategoryAForm
 * @method ModelFyziklaniTeam loadEntity(int $id)
 * @method ModelFyziklaniTeam getEntity()
 */
class ClosePresenter extends BasePresenter {

    use EventEntityTrait;

    /* ******* TITLE ***********/
    public function titleList(): void {
        $this->setTitle(_('Close scoring'));
        $this->setIcon('fa fa-check');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleTeam(int $id): void {
        $this->setTitle(sprintf(_('Close scoring of team "%s"'), $this->loadEntity($id)->name));
        $this->setIcon('fa fa-check-square-o');
    }

    public function titleHard(): void {
        $this->setTitle(_('Hard close submitting'));
        $this->setIcon('fa fa-check');
    }

    /* ******* authorized methods ***********/
    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedTeam(): void {
        $this->setAuthorized($this->eventIsAllowed($this->getModelResource(), 'team'));
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->eventIsAllowed($this->getModelResource(), 'team'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizeHard(): void {
        $this->setAuthorized($this->eventIsAllowed($this->getModelResource(), 'hard'));
    }
    /* *********** ACTIONS **************** */
    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionTeam(int $id): void {
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
    public function actionHard(int $id): void {
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
    protected function getORMService(): ServiceFyziklaniTeam {
        return $this->getServiceFyziklaniTeam();
    }

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return 'fyziklani.close';
    }
}
