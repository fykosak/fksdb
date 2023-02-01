<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Grids\Schedule\PerPersonScheduleList;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

class PersonSchedulePresenter extends BasePresenter
{
    /** @persistent */
    public ?int $personId = null;

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Schedule per person'), 'fas fa-list');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentList(): PerPersonScheduleList
    {
        return new PerPersonScheduleList($this->getContext(), $this->getEvent());
    }
}
