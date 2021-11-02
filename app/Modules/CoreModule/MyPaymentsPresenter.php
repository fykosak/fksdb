<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\UI\PageTitle;

class MyPaymentsPresenter extends BasePresenter
{

    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getUser()->getIdentity()->getPerson());
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('My payments'), 'fa fa-credit-card');
    }

    protected function createComponentMyPaymentGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'payment',
            $this->getUser()->getIdentity()->getPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }
}
