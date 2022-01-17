<?php

declare(strict_types=1);

namespace FKSDB\Tests\MockEnvironment;

use FKSDB\Modules\Core\BasePresenter;

class MockPresenter extends BasePresenter
{
    public function link(string $destination, $args = []): string
    {
        return '';
    }

    public function getLang(): string
    {
        return 'cs';
    }
}
