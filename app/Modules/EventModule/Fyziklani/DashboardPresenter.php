<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Fyziklani game app'), 'fas fa-laptop-code');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.dashboard', 'default'));
    }
}
