<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Game'), 'fas fa-laptop-code');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->isAllowed('game.dashboard', 'default'));
    }
}