<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;

abstract class BasePresenter extends \FKSDB\Modules\EventModule\BasePresenter
{
    /**
     * @throws EventNotFoundException
     */
    protected function getSubTitle(): string
    {
        return _('Schedule of event') . ' ' .
            $this->getEvent()->getName()->getText($this->translator->lang); // @phpstan-ignore-line
    }
}
