<?php

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Grids\Fyziklani\Submits\TeamSubmitsGrid;
use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Fyziklani\Closing\AlreadyClosedException;
use FKSDB\Models\Fyziklani\Closing\NotCheckedSubmitsException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class ClosePresenter
 * @property FormControl closeCategoryAForm
 * @method ModelFyziklaniTeam getEntity()
 */
class ClosePresenter extends BasePresenter {

    use EventEntityPresenterTrait;

    /* ******* TITLE ***********/
    public function getTitleList(): PageTitle {
        return new PageTitle(_('Sealing of the scoring'), 'fa fa-check');
    }

    /**
     * @return void
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleTeam(): void {
        $this->setPageTitle(new PageTitle(\sprintf(_('Sealing of the scoring for the team "%s"'), $this->getEntity()->name), 'fa fa-check-square-o'));
    }

    /**
     * @return void
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
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
     * @param string|null $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isEventOrContestOrgAuthorized($resource, $privilege);
    }
    /* *********** ACTIONS **************** */
    /**
     * @return void
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function actionTeam(): void {
        try {
            $this->getEntity()->canClose();
        } catch (AlreadyClosedException | NotCheckedSubmitsException $exception) {
            $this->flashMessage($exception->getMessage());
            $this->redirect('list');
        }
    }

    /* ********* COMPONENTS ************* */
    /**
     * @return CloseTeamComponent
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentCloseTeamControl(): CloseTeamComponent {
        return new CloseTeamComponent($this->getContext(), $this->getEntity());
    }

    /**
     * @return TeamSubmitsGrid
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentTeamSubmitsGrid(): TeamSubmitsGrid {
        return new TeamSubmitsGrid($this->getEntity(), $this->getContext());
    }

    protected function getORMService(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
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
