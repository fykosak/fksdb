<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\UI\PageTitle;

class PaymentsPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My payments'), 'fa fa-credit-card');
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
