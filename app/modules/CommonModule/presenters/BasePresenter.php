<?php

namespace CommonModule;

use AuthenticatedPresenter;

/**
 * Class BasePresenter
 * @package CoreModule
 */
abstract class BasePresenter extends AuthenticatedPresenter {
    /**
     * @var bool
     * @persistent
     */
    public $darkMode = false;

    /**
     * @return array
     */
    protected function getNavBarVariant(): array {
        return ['common' . ($this->darkMode ? ' common-black' : ''), 'bg-dark navbar-dark'];
    }

    protected function beforeRender() {
        $this->template->darkMode = $this->darkMode;
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
}
