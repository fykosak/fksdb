<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{

    /**
     * @throws EventNotFoundException
     */
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, \sprintf(_('Event %s'), $this->getEvent()->name), 'fas fa-calendar-alt');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->isAllowed('event.dashboard', 'default');
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderDefault(): void
    {
        $this->template->isOrganizer = $this->isAllowed($this->getEvent(), 'edit');
    }
}
