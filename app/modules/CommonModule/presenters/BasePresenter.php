<?php

namespace CommonModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\Choosers\ThemeSwitcher;

/**
 * Class BasePresenter
 * @package CoreModule
 */
abstract class BasePresenter extends AuthenticatedPresenter {
    /**
     * @var bool
     * @persistent
     */
    public $theme = 'light';

    /**
     * @return array
     */
    protected function getNavBarVariant(): array {
        return ['theme-light common', 'bg-dark navbar-dark'];
    }

    protected function beforeRender() {
        $this->template->theme = $this->theme;
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
     * @param $resource
     * @param $privilege
     * @return bool
     */
    protected function isAllowed($resource, $privilege): bool {
        return $this->getContestAuthorizator()->isAllowedForAnyContest($resource, $privilege);
    }
}
