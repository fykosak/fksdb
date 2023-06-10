<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\EntityForms\LoginFomComponent;
use Fykosak\Utils\UI\PageTitle;

class LoginPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Change login'), 'fas fa-user');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    protected function createComponentLoginForm(): LoginFomComponent
    {
        return new LoginFomComponent($this->getContext(), $this->getLoggedPerson()->getLogin());
    }
}
