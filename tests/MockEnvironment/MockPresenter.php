<?php

namespace MockEnvironment;

use BasePresenter;

class MockPresenter extends BasePresenter {

    public function link($destination, $args = []) {
        return '';
    }

    public function getLang(): string {
        return 'cs';
    }
}

