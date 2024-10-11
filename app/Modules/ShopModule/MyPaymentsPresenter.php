<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Components\Payments\MyPaymentsList;
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

    protected function createComponentMyPaymentGrid(): MyPaymentsList
    {
        return new MyPaymentsList($this->getContext(), $this->getLoggedPerson());
    }
}
