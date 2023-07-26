<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{
    public function authorizedDefault(): bool
    {
        /** @var LoginModel|null $login */
        $login = $this->getUser()->getIdentity();
        return $login && count($login->person->getActiveOrgs());
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Organizer\'s dashboard'), 'fas fa-chalkboard');
    }
}
