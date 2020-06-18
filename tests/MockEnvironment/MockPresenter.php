<?php

namespace MockEnvironment;

use FKSDB\Modules\Core\BasePresenter;

class MockPresenter extends BasePresenter {

    public function link($destination, $args = []) {
        return '';
    }

    public function getLang(): string {
        return 'cs';
    }
}

