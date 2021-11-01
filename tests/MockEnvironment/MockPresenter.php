<?php

declare(strict_types=1);

namespace FKSDB\Tests\MockEnvironment;

use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\UI\InvalidLinkException;

class MockPresenter extends BasePresenter
{

    /**
     * Generates URL to presenter, action or signal.
     * @param string $destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
     * @param mixed ...$args
     * @throws InvalidLinkException
     */
    public function link($destination, ...$args): string
    {
        return '';
    }

    public function getLang(): string
    {
        return 'cs';
    }
}
