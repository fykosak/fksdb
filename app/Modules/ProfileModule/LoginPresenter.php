<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\EntityForms\LoginFomComponent;

class LoginPresenter extends BasePresenter
{
    protected function createComponentLoginForm(): LoginFomComponent
    {
        return new LoginFomComponent($this->getContext(), $this->getUser()->getIdentity());
    }
}
