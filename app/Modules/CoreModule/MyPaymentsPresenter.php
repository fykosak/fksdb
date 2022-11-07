<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\UI\PageTitle;

class MyPaymentsPresenter extends BasePresenter
{
    protected function startup(): void
    {
        $this->redirect(':Profile:Payments:');
    }
}
