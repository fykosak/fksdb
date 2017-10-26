<?php

namespace MockEnvironment;

use BasePresenter;

class MockPresenter extends BasePresenter {

    public function link($destination, $args = array()) {
        return '';
    }

    public function getLang() {
        return 'cs';
    }

    public function getSelectedContestSymbol() {
        return '';
    }

    public function getNavRoot() {
        return null;
    }

}

