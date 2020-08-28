<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Fyziklani\Closing\AlreadyClosedException;
use FKSDB\Fyziklani\Closing\NotCheckedSubmitsException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\Components\Grids\Fyziklani\TeamSubmitsGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class ClosePresenter
 * *
 * @property FormControl closeCategoryAForm
 * @method ModelFyziklaniTeam getEntity()
 */
class ClosePresenter extends BasePresenter {

    use EventEntityPresenterTrait;

    /* ******* TITLE ***********/
    public function getTitleList(): PageTitle {
        return new PageTitle(_('Uzavírání bodování'), 'fa fa-check');
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     */
    public function titleTeam(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Uzavírání bodování týmu "%s"'), $this->getEntity()->name), 'fa fa-check-square-o'));
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     */
    public function titleHard(): void {
        $this->titleTeam();
    }

    /* ******* authorized methods ***********/
    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedTeam(): void {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized($this->getModelResource(), 'team'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizeHard(): void {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized($this->getModelResource(), 'hard'));
    }

    /**
     * @param IResource|string|null $resource
     * @param string $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isEventOrContestOrgAuthorized($resource, $privilege);
    }
    /* *********** ACTIONS **************** */
    /**
     * @return void
     * @throws AbortException
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     */
    public function actionTeam(): void {
        try {
            $this->getEntity()->canClose();
        } catch (AlreadyClosedException $exception) {
            $this->flashMessage($exception->getMessage());
            $this->redirect('list');
        } catch (NotCheckedSubmitsException$exception) {
            $this->flashMessage($exception->getMessage());
            $this->redirect('list');
        }
        $this->actionHard();
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     */
    public function actionHard(): void {
        $control = $this->getComponent('closeTeamControl');
        if (!$control instanceof CloseTeamControl) {
            throw new BadTypeException(CloseTeamControl::class, $control);
        }
        $control->setTeam($this->getEntity());
    }

    /* ********* COMPONENTS ************* */

    /**
     * @return CloseTeamControl
     * @throws EventNotFoundException
     */
    protected function createComponentCloseTeamControl(): CloseTeamControl {
        return new CloseTeamControl($this->getContext(), $this->getEvent());
    }

    /**
     * @return TeamSubmitsGrid
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     */
    protected function createComponentTeamSubmitsGrid(): TeamSubmitsGrid {
        return new TeamSubmitsGrid($this->getEntity(), $this->getContext());
    }

    protected function getORMService(): ServiceFyziklaniTeam {
        return $this->getServiceFyziklaniTeam();
    }

    protected function getModelResource(): string {
        return 'fyziklani.close';
    }

    /**
     * @return BaseGrid
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): BaseGrid {
        return new CloseTeamsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return Control
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @return Control
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }
}
