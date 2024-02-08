<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

final class HowToPresenter extends BasePresenter
{
    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->eventAuthorizator->isAllowed('game', 'howTo', $this->getEvent());
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('How to'), 'fas fa-');
    }
}
