<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{

    public function authorizedDefault(): void
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        $access = $login && count($login->person->getActiveOrganisers());
        $this->setAuthorized($access);
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Organiser\'s dashboard'), 'fas fa-chalkboard');
    }
}
