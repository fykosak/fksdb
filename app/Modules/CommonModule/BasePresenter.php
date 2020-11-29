<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use Nette\Security\IResource;

/**
 * Class BasePresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    protected function beforeRender(): void {
        $this->getPageStyleContainer()->styleId = 'theme-light common';
        $this->getPageStyleContainer()->setNavBarClassName('bg-dark navbar-dark');
        parent::beforeRender();
    }

    protected function getNavRoots(): array {
        $roots = parent::getNavRoots();
        $roots[] = 'Common.Dashboard.default';
        return $roots;
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function isAnyContestAuthorized($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowedForAnyContest($resource, $privilege);
    }
}
