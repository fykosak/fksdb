<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Warehouse'), 'fas fa-warehouse');
    }

    public function authorizedDefault(): bool
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        return $login && count($login->person->getActiveOrgs());
    }
}
