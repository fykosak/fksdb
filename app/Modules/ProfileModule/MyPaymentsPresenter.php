<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PaymentModel;
use Fykosak\Utils\UI\PageTitle;

final class MyPaymentsPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My payments'), 'fas fa-credit-card');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    /**
     * @phpstan-return PersonRelatedGrid<PaymentModel>
     */
    protected function createComponentMyPaymentGrid(): PersonRelatedGrid
    {
        /** @phpstan-ignore-next-line */
        return new PersonRelatedGrid(
            'payment',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }
}
