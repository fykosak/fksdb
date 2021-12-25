<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\Components\Grids\Fyziklani\Submits\TeamSubmitsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Fyziklani\Closing\AlreadyClosedException;
use FKSDB\Models\Fyziklani\Closing\NotCheckedSubmitsException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

/**
 * @property FormControl closeCategoryAForm
 * @method ModelFyziklaniTeam getEntity()
 */
class ClosePresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    /* ******* TITLE ***********/
    public function titleList(): PageTitle
    {
        return new PageTitle(_('Sealing of the scoring'), 'fas fa-stamp');
    }

    /**
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleHard(): PageTitle
    {
        return $this->titleTeam();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleTeam(): PageTitle
    {
        return new PageTitle(
            \sprintf(_('Sealing of the scoring for the team "%s"'), $this->getEntity()->name),
            'fas fa-stamp'
        );
    }

    /* ******* authorized methods ***********/

    /**
     * @throws EventNotFoundException
     */
    public function authorizedTeam(): void
    {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized($this->getModelResource(), 'team'));
    }

    protected function getModelResource(): string
    {
        return 'fyziklani.close';
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizeHard(): void
    {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized($this->getModelResource(), 'hard'));
    }
    /* *********** ACTIONS **************** */

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function actionTeam(): void
    {
        try {
            $this->getEntity()->canClose();
        } catch (AlreadyClosedException | NotCheckedSubmitsException $exception) {
            $this->flashMessage($exception->getMessage());
            $this->redirect('list');
        }
    }

    /* ********* COMPONENTS ************* */

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isEventOrContestOrgAuthorized($resource, $privilege);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentCloseTeamControl(): CloseTeamComponent
    {
        return new CloseTeamComponent($this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentTeamSubmitsGrid(): TeamSubmitsGrid
    {
        return new TeamSubmitsGrid($this->getEntity(), $this->getContext());
    }

    protected function getORMService(): ServiceFyziklaniTeam
    {
        return $this->serviceFyziklaniTeam;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): BaseGrid
    {
        return new CloseTeamsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }
}
