<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use Nette\Security\IResource;

/**
 * Class BasePresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    protected function beforeRender() {
        $this->getPageStyleContainer()->styleId = 'theme-light common';
        $this->getPageStyleContainer()->navBarClassName = 'bg-dark navbar-dark';
        parent::beforeRender();
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array {
        $roots = parent::getNavRoots();
        $roots[] = 'Common.Dashboard.default';
        return $roots;

    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    protected function isAnyContestAuthorized($resource, string $privilege): bool {
        return $this->getContestAuthorizator()->isAllowedForAnyContest($resource, $privilege);
    }
}
