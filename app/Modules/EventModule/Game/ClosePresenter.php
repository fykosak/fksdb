<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\Closing\CodeCloseForm;
use FKSDB\Components\Game\Closing\PreviewComponent;
use FKSDB\Components\Game\Closing\TeamList;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

/**
 * @method TeamModel2 getEntity()
 */
final class ClosePresenter extends BasePresenter
{
    /** @phpstan-use EventEntityPresenterTrait<TeamModel2> */
    use EventEntityPresenterTrait;

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource('game', $this->getEvent()),
            'close',
            $this->getEvent()
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Close scoring'), 'fas fa-stamp');
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
     */
    public function authorizedTeam(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource('game', $this->getEvent()),
            'close',
            $this->getEvent()
        );
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

    /**
     * @throws GoneException
     */
    protected function getORMService(): TeamService2
    {
        throw new GoneException();
    }

    protected function loadModel(): TeamModel2
    {
        /** @var TeamModel2|null $candidate */
        $candidate = $this->getEvent()->getTeams()->where('fyziklani_team_id', $this->id)->fetch();
        if ($candidate) {
            return $candidate;
        } else {
            throw new NotFoundException(_('Model does not exist.'));
        }
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TeamList
    {
        return new TeamList($this->getContext(), $this->getEvent());
    }


    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCodeCloseForm(): CodeCloseForm
    {
        return new CodeCloseForm($this->getContext(), $this->getEvent());
    }
}
