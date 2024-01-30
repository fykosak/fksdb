<?php

declare(strict_types=1);

namespace FKSDB\Tests\MockEnvironment;

use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\Core\Language;

class MockPresenter extends BasePresenter
{
    public function link(string $destination, $args = []): string
    {
        return '';
    }

    public function getLang(): Language
    {
        return Language::tryFrom('cs');
    }
}
