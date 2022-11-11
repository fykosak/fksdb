<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

class MyPaymentsPresenter extends BasePresenter
{
    protected function startup(): void
    {
        $this->redirect(':Profile:Payments:');
    }
}
