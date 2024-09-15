<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\TaskGrid;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use Fykosak\Utils\UI\PageTitle;

final class TaskPresenter extends BasePresenter
{
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Tasks'), 'fas fa-tasks');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): bool
    {
        return $this->eventAuthorizator->isAllowed(
            new PseudoEventResource(TaskModel::RESOURCE_ID, $this->getEvent()),
            'list',
            $this->getEvent()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TaskGrid
    {
        return new TaskGrid($this->getEvent(), $this->getContext());
    }
}
