<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\SingleTestComponent;
use FKSDB\Components\DataTest\Tests\Event\Team\CategoryCheck;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
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
        set_time_limit(120);
        $tests = $this->dataTestFactory->getEventTests();
        foreach ($tests as $key => $test) {
            $this->addComponent(new SingleTestComponent($this->getContext(), $test), 'test_' . $key);
        };
        $this->template->event = $this->getEvent();
        $this->template->tests = $tests;
    }

    protected function createComponentTest(): SingleTestComponent
    {
        return new SingleTestComponent($this->getContext(), new CategoryCheck($this->getContext()));
    }
}
