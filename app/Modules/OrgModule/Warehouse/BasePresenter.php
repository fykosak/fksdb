<?php

namespace FKSDB\Modules\OrgModule\Warehouse;

use Nette\Security\Resource;

abstract class BasePresenter extends \FKSDB\Modules\OrgModule\BasePresenter {

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function isAllowed($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getRole(): string {
        return 'org';
    }

    protected function beforeRender(): void {
        $contest = $this->getSelectedContest();
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleId = $contest->getContestSymbol();
            $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-' . $contest->getContestSymbol());
        }
        parent::beforeRender();
    }

    protected function getNavRoots(): array {
        $roots = parent::getNavRoots();
        $roots[] = 'Warehouse.Dashboard.default';
        return $roots;
    }
}
