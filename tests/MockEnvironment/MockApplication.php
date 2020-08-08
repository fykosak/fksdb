<?php

namespace MockEnvironment;

use FKSDB\Modules\Core\BasePresenter;

class MockApplication {

    /**
     * @var BasePresenter
     */
    private $presenter;

    /**
     * MockApplication constructor.
     * @param BasePresenter $presenter
     */
    public function __construct(BasePresenter $presenter) {
        $this->presenter = $presenter;
    }

    /**
     * @return BasePresenter
     */
    public function getPresenter() {
        return $this->presenter;
    }
}
