<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\Utils\UI\PageTitle;

final class GameSetupPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Fyziklani game setup'), 'fas fa-cogs');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->eventAuthorizator->isAllowed(EventModel::RESOURCE_ID, 'gameSetup', $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws NotSetGameParametersException
     */
    final public function renderDefault(): void
    {
        $this->template->gameSetup = $this->getEvent()->getGameSetup();
    }
}
