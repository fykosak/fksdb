<?php

namespace CommonModule;

use AuthenticatedPresenter;
use Nette\Security\IResource;

/**
 * Class BasePresenter
 * @package CoreModule
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    /**
     * @return array
     */
    protected function getNavBarVariant(): array {
        return ['theme-light common', 'bg-dark navbar-dark'];
    }

    protected function beforeRender() {
        parent::beforeRender();
    }

    /**
     * @return array
     */
    protected function getNavRoots(): array {
        $roots = parent::getNavRoots();
        $roots[] = 'common.dashboard.default';
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
