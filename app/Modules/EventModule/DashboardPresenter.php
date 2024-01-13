<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotFoundException;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\Utils\UI\Title;

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
        return $this->eventAuthorizator->isAllowed('event.dashboard', 'default', $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderDefault(): void
    {
        $this->template->isOrganizer = $this->eventAuthorizator->isAllowed(
            $this->getEvent(),
            'edit',
            $this->getEvent()
        );
        try {
            $application = $this->getLoggedPerson()->getApplication($this->getEvent());
            $this->template->applicationNav = new NavItem(
                new Title(null, _('My application'), 'fas fa-check'),
                $this->getEvent()->isTeamEvent() ? ':Event:Team:detail' : ':Event:Application:detail',
                ['id' => $application->getPrimary()]
            );
        } catch (NotFoundException $exception) {
            $this->template->applicationNav = null;
        }
    }
}
