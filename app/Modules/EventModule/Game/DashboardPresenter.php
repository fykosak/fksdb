<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Game'), 'fas fa-laptop-code');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->eventAuthorizator->isAllowed(
            new PseudoEventResource('game', $this->getEvent()),
            'dashboard',
            $this->getEvent()
        );
    }
}
