<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\Closing\AlreadyClosedException;
use FKSDB\Components\Game\Closing\CtyrbojPreviewComponent;
use FKSDB\Components\Game\Closing\FOFPreviewComponent;
use FKSDB\Components\Game\Closing\NotCheckedSubmitsException;
use FKSDB\Components\Game\Closing\PreviewComponent;
use FKSDB\Components\Game\Closing\TeamListComponent;
use FKSDB\Components\Game\Closing\TeamSubmitsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

/**
 * @method TeamModel2 getEntity()
 */
class ClosePresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    /* ******* TITLE ***********/
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Sealing of the scoring'), 'fas fa-stamp');
    }

    /**
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
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
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function titleTeam(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Sealing of the scoring for the team "%s"'), $this->getEntity()->name),
            'fas fa-stamp'
        );
    }

    /* ******* authorized methods ***********/

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedTeam(): void
    {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'default'));
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizeHard(): void
    {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'hard'));
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizeList(): void
    {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'default'));
    }
    /* *********** ACTIONS **************** */

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
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
        return $this->isAllowed($resource, $privilege);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NotImplementedException
     */
    protected function createComponentCloseTeamControl(): PreviewComponent
    {
        switch ($this->getEvent()->event_type_id) {
            case 1:
                return new FOFPreviewComponent($this->getContext(), $this->getEntity());
            case 17:
                return new CtyrbojPreviewComponent($this->getContext(), $this->getEntity());
        }
        throw new NotImplementedException();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentTeamSubmitsGrid(): TeamSubmitsGrid
    {
        return new TeamSubmitsGrid($this->getEntity(), $this->getContext());
    }

    protected function getORMService(): TeamService2
    {
        return $this->teamService;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TeamListComponent
    {
        return new TeamListComponent($this->getContext(), $this->getEvent());
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

    protected function getModelResource(): string
    {
        return 'game.close';
    }
}
