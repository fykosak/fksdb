<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\TaskGrid;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class TaskPresenter extends BasePresenter
{
    /** @use EventEntityPresenterTrait<TaskModel> */
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
        return $this->eventAuthorizator->isAllowed('game.task', 'list', $this->getEvent());
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
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $this->getEvent());
    }

    /**
     * @throws GoneException
     */
    protected function getORMService(): TaskService
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
