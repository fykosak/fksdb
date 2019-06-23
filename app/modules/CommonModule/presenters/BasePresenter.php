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
        return [('theme-' . $this->getComponent('themeSwitcher')->getSelectedTheme()) . ' common', 'bg-dark navbar-dark'];
    }

    protected function beforeRender() {
        $this->template->theme = $this->theme;
        parent::beforeRender();
    }

    /**
     * @return ThemeSwitcher
     */
    public function createComponentThemeSwitcher(): ThemeSwitcher {
        return new ThemeSwitcher($this->session, $this->getTranslator());
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
