<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Nette\Security\Resource;

abstract class BasePresenter extends \FKSDB\Modules\OrgModule\BasePresenter
{

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     * @throws NoContestAvailable
     */
    protected function isAllowed($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    /**
     * @phpstan-return string[]
     */
    protected function getNavRoots(): array
    {
        $roots = parent::getNavRoots();
        $roots[] = 'Warehouse.Dashboard.default';
        return $roots;
    }
}
