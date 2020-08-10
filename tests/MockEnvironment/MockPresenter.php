<?php

namespace MockEnvironment;

use FKSDB\Modules\Core\BasePresenter;

class MockPresenter extends BasePresenter {
    /**
     * @param $destination
     * @param array $args
     * @return string
     */
    public function link($destination, $args = []) {
        return '';
    }

    public function getLang(): string {
        return 'cs';
    }
}

