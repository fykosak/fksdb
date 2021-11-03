<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Models\ORM\Models\ModelLogin;
use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{

    public function authorizedDefault(): void
    {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $access = $login ? $login->isOrg() : false;
        $this->setAuthorized($access);
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Organiser\'s dashboard'), 'fas fa-chalkboard');
    }
}
