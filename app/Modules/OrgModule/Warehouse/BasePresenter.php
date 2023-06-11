<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use Nette\Security\Resource;

abstract class BasePresenter extends \FKSDB\Modules\OrgModule\BasePresenter
{

    /**
     * @param Resource|string|null $resource
     */
    protected function isAllowed($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getNavRoots(): array
    {
        $roots = parent::getNavRoots();
        $roots[] = 'Warehouse.Dashboard.default';
        return $roots;
    }
}
