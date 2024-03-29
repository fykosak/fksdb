<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\Closing\CodeCloseForm;
use FKSDB\Components\Game\Closing\PreviewComponent;
use FKSDB\Components\Game\Closing\TeamList;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
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
final class ClosePresenter extends BasePresenter
{
    /** @phpstan-use EventEntityPresenterTrait<TeamModel2> */
    use EventEntityPresenterTrait;

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Close scoring'), 'fas fa-stamp');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedList(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'close', $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
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

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedTeam(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'close', $this->getEvent());
    }


    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentTeamControl(): PreviewComponent
    {
        return new PreviewComponent($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): TeamService2
    {
        return $this->teamService;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TeamList
    {
        return new TeamList($this->getContext(), $this->getEvent());
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

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCodeCloseForm(): CodeCloseForm
    {
        return new CodeCloseForm($this->getContext(), $this->getEvent());
    }

    protected function getModelResource(): string
    {
        return 'game';
    }
}
