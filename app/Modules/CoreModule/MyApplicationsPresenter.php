<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

class MyApplicationsPresenter extends BasePresenter
{
    protected function startup(): void
    {
        $this->redirect(':Profile:Applications:');
    }
}
