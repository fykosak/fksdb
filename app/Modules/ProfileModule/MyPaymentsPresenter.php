<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Grids\Payment\MyPaymentList;
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

    protected function createComponentMyPaymentGrid(): MyPaymentList
    {
        return new MyPaymentList($this->getContext(), $this->getLoggedPerson());
    }
}
