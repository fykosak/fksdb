<?php

declare(strict_types=1);

namespace FKSDB\Tests\MockEnvironment;

use FKSDB\Modules\Core\BasePresenter;

class MockApplication
{

    private BasePresenter $presenter;

    /**
     * MockApplication constructor.
     * @param BasePresenter $presenter
     */
    public function __construct(BasePresenter $presenter)
    {
        $this->presenter = $presenter;
    }

    public function getPresenter(): BasePresenter
    {
        return $this->presenter;
    }
}
