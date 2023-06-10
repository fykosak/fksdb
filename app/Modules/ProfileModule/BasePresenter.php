<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Modules\Core\AuthenticatedPresenter;

abstract class BasePresenter extends AuthenticatedPresenter
{
    protected function startup(): void
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        if (!$login || !$login->person) {
            $this->redirect(':Core:Authentication:login');
        }
        parent::startup();
    }
}
