<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\TaskGrid;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Service;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

class TaskPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Tasks'), 'fas fa-tasks');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed('game.task', 'list');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TaskGrid
    {
        return new TaskGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    /**
     * @throws GoneException
     */
    protected function getORMService(): Service
    {
        throw new GoneException();
    }

    /**
     * @throws GoneException
     */
    protected function createComponentCreateForm(): Control
    {
        throw new GoneException();
    }

    /**
     * @throws GoneException
     */
    protected function createComponentEditForm(): Control
    {
        throw new GoneException();
    }
}
