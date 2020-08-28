<?php

namespace MockEnvironment;

use FKSDB\Modules\Core\BasePresenter;

class MockApplication {

    private BasePresenter $presenter;

    /**
     * MockApplication constructor.
     * @param BasePresenter $presenter
     */
    public function __construct(BasePresenter $presenter) {
        $this->presenter = $presenter;
    }

    public function getPresenter(): BasePresenter {
        return $this->presenter;
    }
}
