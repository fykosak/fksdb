<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\Utils\UI\PageTitle;

class ReportPresenter extends BasePresenter
{
    private DataTestFactory $dataTestFactory;

    public function inject(DataTestFactory $dataTestFactory): void
    {
        $this->dataTestFactory = $dataTestFactory;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Report'), 'fas fa-calendar-alt');
    }


    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->isAllowed('event', 'edit');
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderDefault(): void
    {
        set_time_limit(-1);
        $this->template->event = $this->getEvent();
    }

    /**
     * @phpstan-return TestsList<EventModel>
     */
    protected function createComponentTests(): TestsList
    {
        return new TestsList($this->getContext(), $this->dataTestFactory->getEventTests());
    }
}
