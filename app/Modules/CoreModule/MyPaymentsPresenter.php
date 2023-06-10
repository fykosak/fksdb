<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\UI\PageTitle;

class MyPaymentsPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My payments'), 'fas fa-credit-card');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    protected function createComponentMyPaymentGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'payment',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }
}
