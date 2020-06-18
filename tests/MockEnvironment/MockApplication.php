<?php

namespace MockEnvironment;

use FKSDB\CoreModule\BasePresenter;

class MockApplication {

    /**
     * @var BasePresenter
     */
    private $presenter;

    public function __construct(BasePresenter $presenter) {
        $this->presenter = $presenter;
    }

    public function getPresenter() {
        return $this->presenter;
    }

}
